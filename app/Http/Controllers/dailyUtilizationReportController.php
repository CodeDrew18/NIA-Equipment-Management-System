<?php

namespace App\Http\Controllers;

use App\Models\TransportationRequestFormModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class dailyUtilizationReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();
        $status = trim((string) ($validated['status'] ?? ''));
        $search = trim((string) ($validated['search'] ?? ''));

        $baseQuery = TransportationRequestFormModel::query()
            ->whereDate('request_date', '>=', $from)
            ->whereDate('request_date', '<=', $to)
            ->whereNotNull('date_time_from')
            ->whereNotNull('date_time_to')
            ->whereNotNull('vehicle_id')
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            }, function ($query) {
                $query->whereIn('status', ['Signed', 'Dispatched']);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('form_id', 'like', '%' . $search . '%')
                        ->orWhere('vehicle_id', 'like', '%' . $search . '%')
                        ->orWhere('driver_name', 'like', '%' . $search . '%')
                        ->orWhere('destination', 'like', '%' . $search . '%')
                        ->orWhere('requested_by', 'like', '%' . $search . '%');
                });
            });

        $reportItems = (clone $baseQuery)
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $metricsItems = (clone $baseQuery)
            ->get(['date_time_from', 'date_time_to', 'vehicle_id']);

        $totalTrips = $metricsItems->count();
        $totalVehicles = $metricsItems
            ->pluck('vehicle_id')
            ->filter(function ($vehicleId) {
                return trim((string) $vehicleId) !== '';
            })
            ->unique()
            ->count();

        $totalHours = $metricsItems->sum(function ($item) {
            if (!$item->date_time_from || !$item->date_time_to) {
                return 0;
            }

            $fromTime = Carbon::parse($item->date_time_from);
            $toTime = Carbon::parse($item->date_time_to);

            return $toTime->greaterThan($fromTime)
                ? round($fromTime->floatDiffInHours($toTime), 2)
                : 0;
        });

        return view('daily_equipment_utilization.daily_equipment_utilization_report', [
            'reportItems' => $reportItems,
            'fromDate' => $from,
            'toDate' => $to,
            'statusFilter' => $status,
            'search' => $search,
            'totalTrips' => $totalTrips,
            'totalVehicles' => $totalVehicles,
            'totalHours' => round((float) $totalHours, 2),
            'statusOptions' => ['Signed', 'Dispatched', 'Rejected', 'To be Signed'],
        ]);
    }
}
