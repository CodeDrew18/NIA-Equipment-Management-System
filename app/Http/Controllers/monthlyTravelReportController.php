<?php

namespace App\Http\Controllers;

use App\Models\TransportationRequestFormModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class monthlyTravelReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $selectedMonth = (string) ($validated['month'] ?? now()->format('Y-m'));
        $loggedInUserName = trim((string) (Auth::user()?->name ?? ''));
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $reportItems = TransportationRequestFormModel::query()
            ->whereDate('request_date', '>=', $monthStart->toDateString())
            ->whereDate('request_date', '<=', $monthEnd->toDateString())
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->when($loggedInUserName !== '', function ($query) use ($loggedInUserName) {
                $query->whereRaw('LOWER(TRIM(driver_name)) = ?', [strtolower($loggedInUserName)]);
            }, function ($query) {
                // If no authenticated name is available, do not expose all driver trips.
                $query->whereRaw('1 = 0');
            })
            ->orderBy('request_date')
            ->orderBy('id')
            ->get([
                'id',
                'form_id',
                'request_date',
                'requested_by',
                'destination',
                'date_time_from',
                'date_time_to',
                'vehicle_id',
                'driver_name',
                'status',
                'business_passengers',
            ]);

        $groupedByDay = $reportItems->groupBy(function (TransportationRequestFormModel $item): string {
            return optional($item->request_date)->format('d') ?? '--';
        });

        $reportRows = collect(range(1, (int) $monthEnd->day))->map(function (int $day) use ($groupedByDay): array {
            $dayLabel = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
            $dayItems = collect($groupedByDay->get($dayLabel, []));
            $hasTrips = $dayItems->isNotEmpty();

            $distanceTotal = $dayItems->sum(function (TransportationRequestFormModel $item): float {
                return (float) ($this->resolveDurationHours($item) ?? 0.0);
            });

            $passengers = $dayItems
                ->flatMap(function (TransportationRequestFormModel $item): array {
                    $businessPassengers = is_array($item->business_passengers) ? $item->business_passengers : [];

                    return collect($businessPassengers)
                        ->map(function ($passenger): string {
                            if (is_array($passenger)) {
                                return trim((string) ($passenger['name'] ?? ''));
                            }

                            return trim((string) $passenger);
                        })
                        ->filter()
                        ->values()
                        ->all();
                })
                ->filter()
                ->unique()
                ->values();

            $destinations = $dayItems
                ->map(fn(TransportationRequestFormModel $item): string => trim((string) ($item->destination ?? '')))
                ->filter()
                ->unique()
                ->values();

            $isIssued = $dayItems->contains(function (TransportationRequestFormModel $item): bool {
                return in_array((string) $item->status, ['Dispatched', 'On Trip', 'For Evaluation'], true);
            });

            return [
                'day' => $dayLabel,
                'distance' => $hasTrips && $distanceTotal > 0 ? round($distanceTotal, 1) : null,
                'diesel' => null,
                'gasoline' => null,
                'engineOil' => null,
                'gearOil' => null,
                'brakeFluid' => null,
                'grease' => null,
                'purchasedIssued' => $hasTrips ? ($isIssued ? 'Issued' : '—') : '—',
                'passenger' => $passengers->isNotEmpty() ? $passengers->implode(', ') : '—',
                'destination' => $destinations->isNotEmpty() ? $destinations->implode('; ') : '—',
            ];
        })->values();

        $driverNames = $reportItems->pluck('driver_name')
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        $derivedPrimaryDriver = (string) ($driverNames->first() ?? 'N/A');
        $primaryDriver = $loggedInUserName !== '' ? $loggedInUserName : $derivedPrimaryDriver;
        $assignedDriver = $primaryDriver;
        $divisionManagerName = 'ENGR. EMILIO M. DOMAGAS JR.';

        $vehiclePlate = (string) (
            $reportItems->pluck('vehicle_id')
            ->map(fn($vehicleId) => trim((string) $vehicleId))
            ->first(fn($vehicleId) => $vehicleId !== '')
            ?? 'N/A'
        );

        $propertyNumber = (string) (
            $reportItems->pluck('form_id')
            ->map(fn($formId) => trim((string) $formId))
            ->first(fn($formId) => $formId !== '')
            ?? 'N/A'
        );

        $totalDistance = (float) $reportRows->sum(function (array $row): float {
            $distance = $row['distance'] ?? null;

            return is_numeric($distance) ? (float) $distance : 0.0;
        });

        return view('monthly_official_travel_report.monthly_travel_report', [
            'selectedMonth' => $selectedMonth,
            'vehiclePlate' => $vehiclePlate,
            'assignedDriver' => $assignedDriver,
            'primaryDriver' => $primaryDriver,
            'divisionManagerName' => $divisionManagerName,
            'propertyNumber' => $propertyNumber,
            'reportRows' => $reportRows,
            'totalDistance' => round($totalDistance, 1),
        ]);
    }

    private function resolveDurationHours(TransportationRequestFormModel $item): ?float
    {
        if (!$item->date_time_from || !$item->date_time_to) {
            return null;
        }

        $from = Carbon::parse($item->date_time_from);
        $to = Carbon::parse($item->date_time_to);

        if (!$to->greaterThan($from)) {
            return null;
        }

        return round($from->floatDiffInHours($to), 1);
    }
}
