<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TransportationRequestFormModel;
use App\Support\TripLifecycleManager;
use Illuminate\Http\Request;

class onTripVehicleController extends Controller
{
    public function index(Request $request, TripLifecycleManager $tripLifecycleManager)
    {
        $tripLifecycleManager->moveFinishedTripsToEvaluationQueue();

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $fromDate = $validated['from'] ?? '';
        $toDate = $validated['to'] ?? '';

        $baseQuery = TransportationRequestFormModel::query()
            ->where('status', 'On Trip')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('form_id', 'like', '%' . $search . '%')
                        ->orWhere('requested_by', 'like', '%' . $search . '%')
                        ->orWhere('destination', 'like', '%' . $search . '%')
                        ->orWhere('vehicle_id', 'like', '%' . $search . '%')
                        ->orWhere('driver_name', 'like', '%' . $search . '%');
                });
            })
            ->when($fromDate !== '', function ($query) use ($fromDate) {
                $query->whereDate('request_date', '>=', $fromDate);
            })
            ->when($toDate !== '', function ($query) use ($toDate) {
                $query->whereDate('request_date', '<=', $toDate);
            });

        $onTripRequests = (clone $baseQuery)
            ->orderByDesc('date_time_from')
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totalOnTrip = (clone $baseQuery)->count();
        $vehiclesDeployed = (clone $baseQuery)
            ->whereNotNull('vehicle_id')
            ->where('vehicle_id', '!=', '')
            ->count();
        $driversAssigned = (clone $baseQuery)
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->count();

        return view('admin.on_trip_vehicles.on_trip_vehicles_process', [
            'onTripRequests' => $onTripRequests,
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalOnTrip' => $totalOnTrip,
            'vehiclesDeployed' => $vehiclesDeployed,
            'driversAssigned' => $driversAssigned,
        ]);
    }
}
