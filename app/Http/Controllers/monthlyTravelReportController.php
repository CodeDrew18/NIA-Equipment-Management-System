<?php

namespace App\Http\Controllers;

use App\Models\TransportationRequestFormModel;
use App\Support\AssignatoryPersonnelResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class monthlyTravelReportController extends Controller
{
    public function index(Request $request)
    {
        return view('monthly_official_travel_report.monthly_travel_report', $this->buildReportData($request));
    }

    public function download(Request $request): StreamedResponse
    {
        $reportData = $this->buildReportData($request);
        $selectedMonth = (string) ($reportData['selectedMonth'] ?? now()->format('Y-m'));
        $monthLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
        $driverName = (string) ($reportData['primaryDriver'] ?? 'N/A');
        $reportRows = collect($reportData['reportRows'] ?? []);

        $driverSlug = Str::slug($driverName);
        if ($driverSlug === '') {
            $driverSlug = 'driver';
        }

        $fileName = 'monthly_official_travel_report_' . $driverSlug . '_' . $selectedMonth . '.csv';

        return response()->streamDownload(function () use ($reportData, $monthLabel, $driverName, $reportRows) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Monthly Official Travel Report']);
            fputcsv($handle, ['Month', $monthLabel]);
            fputcsv($handle, ['Driver', $driverName]);
            fputcsv($handle, ['Vehicle Plate', (string) ($reportData['vehiclePlate'] ?? 'N/A')]);
            fputcsv($handle, ['Property Number', (string) ($reportData['propertyNumber'] ?? 'N/A')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Date',
                'Distance (Kms/Hrs)',
                'Diesel (Ltrs)',
                'Gasoline (Ltrs)',
                'E.O (Ltrs)',
                'G.O (Ltrs)',
                'BF (Ltrs)',
                'Grease (Kgs)',
                'Purchased/Issued',
                'Passenger',
                'Destination/Place',
            ]);

            foreach ($reportRows as $row) {
                fputcsv($handle, [
                    (string) ($row['day'] ?? '—'),
                    $this->formatMetricForExport($row['distance'] ?? null),
                    $this->formatMetricForExport($row['diesel'] ?? null),
                    $this->formatMetricForExport($row['gasoline'] ?? null),
                    $this->formatMetricForExport($row['engineOil'] ?? null),
                    $this->formatMetricForExport($row['gearOil'] ?? null),
                    $this->formatMetricForExport($row['brakeFluid'] ?? null),
                    $this->formatMetricForExport($row['grease'] ?? null),
                    (string) ($row['purchasedIssued'] ?? '—'),
                    (string) ($row['passenger'] ?? '—'),
                    (string) ($row['destination'] ?? '—'),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'Total',
                $this->formatMetricForExport($reportData['totalDistance'] ?? null),
                $this->formatMetricForExport($reportData['totalDiesel'] ?? null),
                $this->formatMetricForExport($reportData['totalGasoline'] ?? null),
                $this->formatMetricForExport($reportData['totalEngineOil'] ?? null),
                $this->formatMetricForExport($reportData['totalGearOil'] ?? null),
                $this->formatMetricForExport($reportData['totalBrakeFluid'] ?? null),
                $this->formatMetricForExport($reportData['totalGrease'] ?? null),
                '',
                '',
                'Consolidated Equipment Metrics',
            ]);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildReportData(Request $request): array
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $selectedMonth = (string) ($validated['month'] ?? now()->format('Y-m'));
        $loggedInUserName = trim((string) ($request->user()?->name ?? Auth::user()?->name ?? ''));
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $candidateItems = TransportationRequestFormModel::query()
            ->with([
                'dailyDriversTripTicket:id,transportation_request_form_id,request_form_data,distance_travelled,odometer_start,odometer_end,fuel_total,fuel_issued_regional,fuel_purchased_trip,fuel_issued_nia,gear_oil_liters,engine_oil_liters,grease_kgs',
            ])
            ->whereDate('request_date', '>=', $monthStart->toDateString())
            ->whereDate('request_date', '<=', $monthEnd->toDateString())
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
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

        $reportItems = $loggedInUserName !== ''
            ? $candidateItems->filter(function (TransportationRequestFormModel $item) use ($loggedInUserName): bool {
                return $this->containsDriverName((string) ($item->driver_name ?? ''), $loggedInUserName);
            })->values()
            : collect();

        $groupedByDay = $reportItems->groupBy(function (TransportationRequestFormModel $item): string {
            return optional($item->request_date)->format('d') ?? '--';
        });

        $reportRows = collect(range(1, (int) $monthEnd->day))->map(function (int $day) use ($groupedByDay): array {
            $dayLabel = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
            $dayItems = collect($groupedByDay->get($dayLabel, []));
            $hasTrips = $dayItems->isNotEmpty();

            $distanceMetric = $this->aggregateMetric($dayItems, function (TransportationRequestFormModel $item): ?float {
                return $this->resolveDistanceMetric($item);
            });

            $dieselMetric = $this->aggregateMetric($dayItems, function (TransportationRequestFormModel $item): ?float {
                return $this->resolveFuelLitersByKind($item, 'diesel');
            });

            $gasolineMetric = $this->aggregateMetric($dayItems, function (TransportationRequestFormModel $item): ?float {
                return $this->resolveFuelLitersByKind($item, 'gasoline');
            });

            $engineOilMetric = $this->aggregateMetric($dayItems, function (TransportationRequestFormModel $item): ?float {
                return $this->resolveEngineOilLiters($item);
            });

            $gearOilMetric = $this->aggregateMetric($dayItems, function (TransportationRequestFormModel $item): ?float {
                return $this->resolveGearOilLiters($item);
            });

            $brakeFluidMetric = $this->aggregateMetric($dayItems, function (TransportationRequestFormModel $item): ?float {
                return $this->resolveBrakeFluidLiters($item);
            });

            $greaseMetric = $this->aggregateMetric($dayItems, function (TransportationRequestFormModel $item): ?float {
                return $this->resolveGreaseKilograms($item);
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
                'distance' => $this->finalizeMetric($distanceMetric),
                'diesel' => $this->finalizeMetric($dieselMetric),
                'gasoline' => $this->finalizeMetric($gasolineMetric),
                'engineOil' => $this->finalizeMetric($engineOilMetric),
                'gearOil' => $this->finalizeMetric($gearOilMetric),
                'brakeFluid' => $this->finalizeMetric($brakeFluidMetric),
                'grease' => $this->finalizeMetric($greaseMetric),
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
        $assignatory = AssignatoryPersonnelResolver::resolve();
        $divisionManagerName = (string) ($assignatory['name'] ?? 'N/A');
        $divisionManagerPosition = (string) ($assignatory['position'] ?? 'Division Manager');

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

        return [
            'selectedMonth' => $selectedMonth,
            'vehiclePlate' => $vehiclePlate,
            'assignedDriver' => $assignedDriver,
            'primaryDriver' => $primaryDriver,
            'divisionManagerName' => $divisionManagerName,
            'divisionManagerPosition' => $divisionManagerPosition,
            'propertyNumber' => $propertyNumber,
            'reportRows' => $reportRows,
            'totalDistance' => round($totalDistance, 1),
            'totalDiesel' => $this->sumReportRowsMetric($reportRows, 'diesel'),
            'totalGasoline' => $this->sumReportRowsMetric($reportRows, 'gasoline'),
            'totalEngineOil' => $this->sumReportRowsMetric($reportRows, 'engineOil'),
            'totalGearOil' => $this->sumReportRowsMetric($reportRows, 'gearOil'),
            'totalBrakeFluid' => $this->sumReportRowsMetric($reportRows, 'brakeFluid'),
            'totalGrease' => $this->sumReportRowsMetric($reportRows, 'grease'),
        ];
    }

    private function sumReportRowsMetric(Collection $rows, string $key): float
    {
        $total = $rows->sum(function (array $row) use ($key): float {
            $metric = $row[$key] ?? null;

            return is_numeric($metric) ? (float) $metric : 0.0;
        });

        return round((float) $total, 1);
    }

    private function formatMetricForExport(mixed $metric): string
    {
        if (!is_numeric($metric)) {
            return '—';
        }

        return number_format((float) $metric, 1, '.', '');
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

    private function resolveDistanceMetric(TransportationRequestFormModel $item): ?float
    {
        $ticket = $item->dailyDriversTripTicket;
        $snapshot = $this->decodeSnapshot($ticket?->request_form_data);

        $distance = $this->toNullableFloat($ticket?->distance_travelled)
            ?? $this->readNumericFromArray($snapshot, ['distance_travelled', 'distanceTravelled', 'distance']);

        if ($distance !== null) {
            return $distance;
        }

        $odometerStart = $this->toNullableFloat($ticket?->odometer_start)
            ?? $this->readNumericFromArray($snapshot, ['odometer_start', 'odometerStart']);
        $odometerEnd = $this->toNullableFloat($ticket?->odometer_end)
            ?? $this->readNumericFromArray($snapshot, ['odometer_end', 'odometerEnd']);

        if ($odometerStart !== null && $odometerEnd !== null) {
            return max(0.0, round($odometerEnd - $odometerStart, 2));
        }

        return $this->resolveDurationHours($item);
    }

    private function resolveFuelLitersByKind(TransportationRequestFormModel $item, string $fuelKind): ?float
    {
        $ticket = $item->dailyDriversTripTicket;
        $snapshot = $this->decodeSnapshot($ticket?->request_form_data);

        $explicitFuel = $fuelKind === 'diesel'
            ? $this->readNumericFromArray($snapshot, ['diesel', 'diesel_liters', 'dieselFuelLiters', 'diesel_fuel_liters'])
            : $this->readNumericFromArray($snapshot, ['gasoline', 'gasoline_liters', 'gasolineFuelLiters', 'gasoline_fuel_liters']);

        if ($explicitFuel !== null) {
            return $explicitFuel;
        }

        $fuelTotal = $this->toNullableFloat($ticket?->fuel_total);
        if ($fuelTotal === null) {
            $componentValues = [
                $this->toNullableFloat($ticket?->fuel_issued_regional),
                $this->toNullableFloat($ticket?->fuel_purchased_trip),
                $this->toNullableFloat($ticket?->fuel_issued_nia),
            ];

            $hasComponent = false;
            $componentTotal = 0.0;
            foreach ($componentValues as $componentValue) {
                if ($componentValue === null) {
                    continue;
                }

                $hasComponent = true;
                $componentTotal += $componentValue;
            }

            if ($hasComponent) {
                $fuelTotal = $componentTotal;
            }
        }

        if ($fuelTotal === null) {
            return null;
        }

        $inferredFuelKind = $this->inferFuelKind($item, $snapshot);
        if ($inferredFuelKind === null) {
            return $fuelKind === 'diesel' ? $fuelTotal : null;
        }

        return $inferredFuelKind === $fuelKind ? $fuelTotal : null;
    }

    private function inferFuelKind(TransportationRequestFormModel $item, array $snapshot): ?string
    {
        $fuelType = strtolower(trim((string) ($snapshot['fuel_type'] ?? $snapshot['fuelType'] ?? $snapshot['fuel_kind'] ?? $snapshot['fuelKind'] ?? '')));
        if ($fuelType !== '') {
            if (str_contains($fuelType, 'diesel')) {
                return 'diesel';
            }

            if (str_contains($fuelType, 'gas')) {
                return 'gasoline';
            }
        }

        $vehicleType = strtolower(trim((string) ($item->vehicle_type ?? $snapshot['vehicle_type'] ?? $snapshot['vehicleType'] ?? '')));
        if ($vehicleType === '') {
            return null;
        }

        if (str_contains($vehicleType, 'pickup') || str_contains($vehicleType, 'pick-up')) {
            return 'gasoline';
        }

        if (str_contains($vehicleType, 'coaster') || str_contains($vehicleType, 'van') || str_contains($vehicleType, 'truck') || str_contains($vehicleType, 'bus')) {
            return 'diesel';
        }

        return null;
    }

    private function resolveEngineOilLiters(TransportationRequestFormModel $item): ?float
    {
        $ticket = $item->dailyDriversTripTicket;
        $snapshot = $this->decodeSnapshot($ticket?->request_form_data);

        return $this->toNullableFloat($ticket?->engine_oil_liters)
            ?? $this->readNumericFromArray($snapshot, ['engine_oil_liters', 'engineOilLiters', 'engine_oil', 'engineOil']);
    }

    private function resolveGearOilLiters(TransportationRequestFormModel $item): ?float
    {
        $ticket = $item->dailyDriversTripTicket;
        $snapshot = $this->decodeSnapshot($ticket?->request_form_data);

        return $this->toNullableFloat($ticket?->gear_oil_liters)
            ?? $this->readNumericFromArray($snapshot, ['gear_oil_liters', 'gearOilLiters', 'gear_oil', 'gearOil']);
    }

    private function resolveBrakeFluidLiters(TransportationRequestFormModel $item): ?float
    {
        $ticket = $item->dailyDriversTripTicket;
        $snapshot = $this->decodeSnapshot($ticket?->request_form_data);

        return $this->readNumericFromArray($snapshot, ['brake_fluid_liters', 'brakeFluidLiters', 'brake_fluid', 'brakeFluid', 'bf']);
    }

    private function resolveGreaseKilograms(TransportationRequestFormModel $item): ?float
    {
        $ticket = $item->dailyDriversTripTicket;
        $snapshot = $this->decodeSnapshot($ticket?->request_form_data);

        return $this->toNullableFloat($ticket?->grease_kgs)
            ?? $this->readNumericFromArray($snapshot, ['grease_kgs', 'greaseKgs', 'grease']);
    }

    private function aggregateMetric(Collection $items, callable $resolver): array
    {
        $total = 0.0;
        $hasValue = false;

        foreach ($items as $item) {
            $resolved = $resolver($item);

            if ($resolved === null) {
                continue;
            }

            $hasValue = true;
            $total += (float) $resolved;
        }

        return [
            'total' => $total,
            'hasValue' => $hasValue,
        ];
    }

    private function finalizeMetric(array $metric): ?float
    {
        if (!($metric['hasValue'] ?? false)) {
            return null;
        }

        return round((float) ($metric['total'] ?? 0.0), 1);
    }

    private function decodeSnapshot(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function readNumericFromArray(array $source, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $source)) {
                continue;
            }

            $value = $this->toNullableFloat($source[$key]);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function containsDriverName(string $driverNamesValue, string $targetName): bool
    {
        $needle = strtolower(trim($targetName));
        if ($needle === '') {
            return false;
        }

        $parsedNames = $this->extractDriverNames($driverNamesValue);

        return collect($parsedNames)
            ->map(function (string $name): string {
                return strtolower(trim($name));
            })
            ->contains($needle);
    }

    private function extractDriverNames(string $value): array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            $tokens = $decoded;
        } else {
            $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return collect($tokens)
            ->map(function ($token): string {
                if (is_array($token)) {
                    return trim((string) ($token['driver_name'] ?? $token['name'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $name): bool {
                return $name !== '';
            })
            ->unique()
            ->values()
            ->all();
    }
}
