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
        $payload = $this->buildPayload($request, $tripLifecycleManager);

        return view('admin.on_trip_vehicles.on_trip_vehicles_process', [
            'onTripRequests' => $payload['onTripRequests'],
            'search' => $payload['search'],
            'fromDate' => $payload['fromDate'],
            'toDate' => $payload['toDate'],
            'totalOnTrip' => $payload['totalOnTrip'],
            'vehiclesDeployed' => $payload['vehiclesDeployed'],
            'driversAssigned' => $payload['driversAssigned'],
        ]);
    }

    public function data(Request $request, TripLifecycleManager $tripLifecycleManager)
    {
        $payload = $this->buildPayload($request, $tripLifecycleManager);
        $requests = $payload['onTripRequests'];

        return response()->json([
            'filters' => [
                'search' => $payload['search'],
                'from' => $payload['fromDate'],
                'to' => $payload['toDate'],
            ],
            'metrics' => [
                'totalOnTrip' => $payload['totalOnTrip'],
                'vehiclesDeployed' => $payload['vehiclesDeployed'],
                'driversAssigned' => $payload['driversAssigned'],
            ],
            'summaryText' => 'Showing ' . ($requests->firstItem() ?? 0) . ' to ' . ($requests->lastItem() ?? 0) . ' of ' . $requests->total() . ' entries',
            'pagination' => [
                'currentPage' => $requests->currentPage(),
                'lastPage' => $requests->lastPage(),
                'onFirstPage' => $requests->onFirstPage(),
                'hasMorePages' => $requests->hasMorePages(),
                'pageUrls' => $requests->getUrlRange(1, $requests->lastPage()),
            ],
            'requests' => $requests->getCollection()->values()->map(function (TransportationRequestFormModel $item) {
                return [
                    'id' => $item->id,
                    'formId' => (string) ($item->form_id ?: 'N/A'),
                    'vehicleId' => (string) ($item->vehicle_id ?: 'N/A'),
                    'driverName' => (string) ($item->driver_name ?: 'N/A'),
                    'requestDate' => optional($item->request_date)->format('M d, Y') ?: 'N/A',
                    'dateTimeTo' => optional($item->date_time_to)->format('M d, Y h:i A') ?: 'N/A',
                    'viewCopyUrl' => route('admin.fuel_issuance_slip', ['request_id' => $item->id]),
                ];
            }),
        ]);
    }

    private function buildPayload(Request $request, TripLifecycleManager $tripLifecycleManager): array
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

        return [
            'onTripRequests' => $onTripRequests,
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalOnTrip' => $totalOnTrip,
            'vehiclesDeployed' => $vehiclesDeployed,
            'driversAssigned' => $driversAssigned,
        ];
    }
}
