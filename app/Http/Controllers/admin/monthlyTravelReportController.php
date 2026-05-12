<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DailyDriversTripTicket;
use App\Models\User;
use App\Support\AssignatoryPersonnelResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class monthlyTravelReportController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.monthly_official_travel_report.monthly_travel_report', $this->buildReportData($request));
    }

    public function download(Request $request): StreamedResponse
    {
        $reportData = $this->buildReportData($request);
        $selectedMonth = (string) ($reportData['selectedMonth'] ?? now()->format('Y-m'));
        $monthLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
        $driverName = (string) ($reportData['selectedDriver'] ?? $reportData['primaryDriver'] ?? 'N/A');
        $reportRows = collect($reportData['reportRows'] ?? []);
        $templatePath = $this->resolveMonthlyTravelTemplatePath();

        $fileName = 'monthly_official_travel_report_' . $selectedMonth . '_' . $driverName . '.xlsx';

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(false);
        $reader->setIncludeCharts(false);
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A9', 'For the month of ' . $monthLabel);
        $sheet->setCellValue('C10', $this->formatTextForTemplate($reportData['vehiclePlate'] ?? null));
        $sheet->setCellValue('C11', $this->formatTextForTemplate($driverName));
        $sheet->setCellValue('M11', $this->formatTextForTemplate($reportData['propertyNumber'] ?? null));

        $rowsByDay = $reportRows
            ->filter(fn($row): bool => is_array($row))
            ->keyBy(function (array $row): string {
                return (string) ((int) ($row['day'] ?? 0));
            });

        for ($day = 1; $day <= 31; $day++) {
            $templateRow = 13 + $day;
            $row = (array) ($rowsByDay->get((string) $day, []));
            $purchasedIssued = strtolower(trim((string) ($row['purchasedIssued'] ?? '')));

            $sheet->setCellValue('B' . $templateRow, $this->formatMetricForTemplate($row['distance'] ?? null));
            $sheet->setCellValue('C' . $templateRow, '');
            $sheet->setCellValue('D' . $templateRow, $this->formatMetricForTemplate($row['diesel'] ?? null));
            $sheet->setCellValue('E' . $templateRow, $this->formatMetricForTemplate($row['gasoline'] ?? null));
            $sheet->setCellValue('F' . $templateRow, $this->formatMetricForTemplate($row['engineOil'] ?? null));
            $sheet->setCellValue('G' . $templateRow, $this->formatMetricForTemplate($row['gearOil'] ?? null));
            $sheet->setCellValue('H' . $templateRow, $this->formatMetricForTemplate($row['brakeFluid'] ?? null));
            $sheet->setCellValue('I' . $templateRow, $this->formatMetricForTemplate($row['grease'] ?? null));
            $sheet->setCellValue('J' . $templateRow, $purchasedIssued === 'purchased' ? 'X' : '');
            $sheet->setCellValue('K' . $templateRow, $purchasedIssued === 'issued' ? 'X' : '');
            $sheet->setCellValue('M' . $templateRow, $this->formatTextForTemplate($row['passenger'] ?? null));
            $sheet->setCellValue('N' . $templateRow, $this->formatTextForTemplate($row['destination'] ?? null));
        }

        $sheet->setCellValue('B45', $this->formatMetricForTemplate($reportData['totalDistance'] ?? null));
        $sheet->setCellValue('C45', '');
        $sheet->setCellValue('D45', $this->formatMetricForTemplate($reportData['totalDiesel'] ?? null));
        $sheet->setCellValue('E45', $this->formatMetricForTemplate($reportData['totalGasoline'] ?? null));
        $sheet->setCellValue('F45', $this->formatMetricForTemplate($reportData['totalEngineOil'] ?? null));
        $sheet->setCellValue('G45', $this->formatMetricForTemplate($reportData['totalGearOil'] ?? null));
        $sheet->setCellValue('H45', $this->formatMetricForTemplate($reportData['totalBrakeFluid'] ?? null));
        $sheet->setCellValue('I45', $this->formatMetricForTemplate($reportData['totalGrease'] ?? null));

        $sheet->setCellValue('G48', $this->formatTextForTemplate($driverName));
        $sheet->setCellValue('D50', $this->formatTextForTemplate($reportData['primaryDriver'] ?? null));

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer, $spreadsheet) {
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function resolveMonthlyTravelTemplatePath(): string
    {
        $candidatePaths = [
            public_path('storage/forms/monthly_official_travel_report.xlsx'),
            storage_path('app/public/forms/monthly_official_travel_report.xlsx'),
        ];

        foreach ($candidatePaths as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        abort(500, 'Monthly official travel report template not found: public/storage/forms/monthly_official_travel_report.xlsx');
    }

    private function buildReportData(Request $request): array
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'driver' => ['nullable', 'string'],
        ]);

        $selectedMonth = (string) ($validated['month'] ?? now()->format('Y-m'));
        $loggedInUserName = trim((string) ($request->user()?->name ?? Auth::user()?->name ?? ''));
        $requestedDriver = trim((string) ($validated['driver'] ?? ''));
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();

        $candidateTickets = DailyDriversTripTicket::query()
            ->with(['transportationRequestForm:id,request_date,vehicle_type,vehicle_id,driver_name,status,date_time_from,date_time_to'])
            ->whereHas('transportationRequestForm', function ($query) use ($monthStart, $monthEnd) {
                $query->whereDate('request_date', '>=', $monthStart->toDateString())
                    ->whereDate('request_date', '<=', $monthEnd->toDateString());
            })
            ->orderBy('id')
            ->get([
                'id',
                'transportation_request_form_id',
                'assigned_driver_name',
                'assigned_vehicle_code',
                'request_form_data',
                'distance_travelled',
                'odometer_start',
                'odometer_end',
                'fuel_balance_before',
                'fuel_total',
                'fuel_issued_regional',
                'fuel_purchased_trip',
                'fuel_issued_nia',
                'fuel_used',
                'fuel_balance_after',
                'gear_oil_liters',
                'engine_oil_liters',
                'grease_kgs',
            ]);

        $driverOptions = $this->buildDriverOptions();
        $driverOptionValues = $driverOptions->pluck('value')->filter();

        $selectedDriver = $requestedDriver !== '' ? $requestedDriver : $loggedInUserName;

        if ($selectedDriver !== '') {
            $selectedDriverLower = strtolower($selectedDriver);
            $normalizedOptions = $driverOptionValues->map(fn(string $name): string => strtolower($name));

            if (!$normalizedOptions->contains($selectedDriverLower)) {
                $selectedDriver = (string) ($driverOptionValues->first() ?? '');
            } else {
                $selectedDriver = (string) ($driverOptionValues->first(function (string $name) use ($selectedDriverLower): bool {
                    return strtolower($name) === $selectedDriverLower;
                }) ?? $selectedDriver);
            }
        } else {
            $selectedDriver = (string) ($driverOptionValues->first() ?? '');
        }

        $reportTickets = $selectedDriver !== ''
            ? $candidateTickets->filter(function (DailyDriversTripTicket $ticket) use ($selectedDriver): bool {
                return $this->containsDriverName($this->resolveTripDriverName($ticket), $selectedDriver);
            })->values()
            : collect();

        $groupedByDay = $reportTickets
            ->map(function (DailyDriversTripTicket $ticket): ?array {
                $requestDate = $this->resolveRequestDate($ticket);
                if (!$requestDate) {
                    return null;
                }

                return [
                    'day' => $requestDate->format('d'),
                    'ticket' => $ticket,
                ];
            })
            ->filter()
            ->groupBy('day')
            ->map(function (Collection $items): Collection {
                return $items->pluck('ticket');
            });

        $reportRows = collect(range(1, (int) $monthEnd->day))->map(function (int $day) use ($groupedByDay): array {
            $dayLabel = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
            $dayTickets = collect($groupedByDay->get($dayLabel, collect()));
            $hasTrips = $dayTickets->isNotEmpty();

            $distanceMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveDistanceMetric($ticket);
            });

            $dieselMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveFuelLitersByKind($ticket, 'diesel');
            });

            $gasolineMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveFuelLitersByKind($ticket, 'gasoline');
            });

            $dieselPurchasedMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveDieselPurchasedLiters($ticket);
            });

            $dieselIssuedMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveDieselIssuedLiters($ticket);
            });

            $dieselConsumedMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveDieselConsumedLiters($ticket);
            });

            $dieselBalanceAfter = $this->resolveLatestMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveDieselBalanceAfterLiters($ticket);
            });

            $engineOilMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveEngineOilLiters($ticket);
            });

            $gearOilMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveGearOilLiters($ticket);
            });

            $brakeFluidMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveBrakeFluidLiters($ticket);
            });

            $greaseMetric = $this->aggregateMetric($dayTickets, function (DailyDriversTripTicket $ticket): ?float {
                return $this->resolveGreaseKilograms($ticket);
            });

            $passengers = $dayTickets
                ->flatMap(function (DailyDriversTripTicket $ticket): array {
                    return $this->resolvePassengerNames($ticket);
                })
                ->filter()
                ->unique()
                ->values();

            $destinations = $dayTickets
                ->map(function (DailyDriversTripTicket $ticket): string {
                    return $this->resolveDestination($ticket);
                })
                ->filter()
                ->unique()
                ->values();

            $isIssued = $dayTickets->contains(function (DailyDriversTripTicket $ticket): bool {
                return in_array($this->resolveTripStatus($ticket), ['Dispatched', 'On Trip', 'For Evaluation'], true);
            });

            return [
                'day' => $dayLabel,
                'distance' => $this->finalizeMetric($distanceMetric),
                'diesel' => $this->finalizeMetric($dieselMetric),
                'gasoline' => $this->finalizeMetric($gasolineMetric),
                'dieselPurchased' => $this->finalizeMetric($dieselPurchasedMetric),
                'dieselIssued' => $this->finalizeMetric($dieselIssuedMetric),
                'dieselConsumed' => $this->finalizeMetric($dieselConsumedMetric),
                'dieselBalanceAfter' => $dieselBalanceAfter,
                'engineOil' => $this->finalizeMetric($engineOilMetric),
                'gearOil' => $this->finalizeMetric($gearOilMetric),
                'brakeFluid' => $this->finalizeMetric($brakeFluidMetric),
                'grease' => $this->finalizeMetric($greaseMetric),
                'purchasedIssued' => $hasTrips ? ($isIssued ? 'Issued' : "\u{2014}") : "\u{2014}",
                'passenger' => $passengers->isNotEmpty() ? $passengers->implode(', ') : "\u{2014}",
                'destination' => $destinations->isNotEmpty() ? $destinations->implode('; ') : "\u{2014}",
            ];
        })->values();

        $driverNames = $reportTickets->map(function (DailyDriversTripTicket $ticket): string {
            return $this->resolveTripDriverName($ticket);
        })
            ->filter()
            ->unique()
            ->values();

        $derivedPrimaryDriver = (string) ($driverNames->first() ?? 'N/A');
        $primaryDriver = $selectedDriver !== '' ? $selectedDriver : $derivedPrimaryDriver;
        $assignedDriver = $primaryDriver;
        $assignatory = AssignatoryPersonnelResolver::resolve();
        $divisionManagerName = (string) ($assignatory['name'] ?? 'N/A');
        $divisionManagerPosition = (string) ($assignatory['position'] ?? 'Division Manager');

        $selectedDriverLower = strtolower($selectedDriver);
        $selectedOption = $driverOptions->first(function (array $option) use ($selectedDriverLower): bool {
            return strtolower((string) ($option['value'] ?? '')) === $selectedDriverLower;
        });

        $vehiclePlate = (string) (
            $reportTickets->map(function (DailyDriversTripTicket $ticket): string {
                return $this->resolveVehiclePlate($ticket);
            })->filter()->first()
            ?? ($selectedOption['vehiclePlate'] ?? '')
            ?? 'N/A'
        );

        $propertyNumber = (string) (
            $reportTickets->map(function (DailyDriversTripTicket $ticket): string {
                return $this->resolvePropertyNumber($ticket);
            })->filter()->first()
            ?? ($selectedOption['propertyNumber'] ?? '')
            ?? ''
        );

        $totalDistance = (float) $reportRows->sum(function (array $row): float {
            $distance = $row['distance'] ?? null;

            return is_numeric($distance) ? (float) $distance : 0.0;
        });

        return [
            'selectedMonth' => $selectedMonth,
            'selectedDriver' => $selectedDriver,
            'driverOptions' => $driverOptions,
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
            'totalDieselPurchased' => $this->sumReportRowsMetric($reportRows, 'dieselPurchased'),
            'totalDieselIssued' => $this->sumReportRowsMetric($reportRows, 'dieselIssued'),
            'totalDieselConsumed' => $this->sumReportRowsMetric($reportRows, 'dieselConsumed'),
            'latestDieselBalanceAfter' => $this->resolveLatestRowMetric($reportRows, 'dieselBalanceAfter'),
            'totalEngineOil' => $this->sumReportRowsMetric($reportRows, 'engineOil'),
            'totalGearOil' => $this->sumReportRowsMetric($reportRows, 'gearOil'),
            'totalBrakeFluid' => $this->sumReportRowsMetric($reportRows, 'brakeFluid'),
            'totalGrease' => $this->sumReportRowsMetric($reportRows, 'grease'),
        ];
    }

    private function buildDriverOptions(): Collection
    {
        $options = [];

        $tickets = DailyDriversTripTicket::query()
            ->with(['transportationRequestForm:id,vehicle_id,driver_name'])
            ->orderByDesc('id')
            ->get([
                'id',
                'transportation_request_form_id',
                'assigned_driver_name',
                'assigned_vehicle_code',
                'request_form_data',
            ]);

        foreach ($tickets as $ticket) {
            $baseDriver = $this->resolveTripDriverName($ticket);
            $driverNamesFromTicket = $this->extractDriverNames($baseDriver);

            foreach ($driverNamesFromTicket as $driverName) {
                $normalized = strtolower($driverName);
                if ($normalized === '' || isset($options[$normalized])) {
                    continue;
                }

                $vehiclePlate = $this->resolveVehiclePlate($ticket);
                $propertyNumber = $this->resolvePropertyNumber($ticket);

                $options[$normalized] = [
                    'value' => $driverName,
                    'label' => $this->buildDriverOptionLabel($driverName, $vehiclePlate, $propertyNumber),
                    'vehiclePlate' => $vehiclePlate,
                    'propertyNumber' => $propertyNumber,
                ];
            }
        }

        $driverUsers = User::query()
            ->whereRaw("CONCAT(',', role, ',') LIKE '%,driver,%'")
            ->orderBy('name')
            ->get(['name']);

        foreach ($driverUsers as $driverUser) {
            $driverName = trim((string) ($driverUser->name ?? ''));
            $normalized = strtolower($driverName);
            if ($normalized === '' || isset($options[$normalized])) {
                continue;
            }

            $options[$normalized] = [
                'value' => $driverName,
                'label' => $driverName,
                'vehiclePlate' => '',
                'propertyNumber' => '',
            ];
        }

        return collect($options)
            ->sortBy(function (array $option): string {
                return strtolower((string) ($option['value'] ?? ''));
            })
            ->values();
    }

    private function buildDriverOptionLabel(string $driverName, string $vehiclePlate, string $propertyNumber): string
    {
        $label = $driverName;
        $details = [];

        if ($vehiclePlate !== '') {
            $details[] = 'Plate: ' . $vehiclePlate;
        }

        if ($propertyNumber !== '') {
            $details[] = 'Property: ' . $propertyNumber;
        }

        if (!empty($details)) {
            $label .= ' (' . implode(' | ', $details) . ')';
        }

        return $label;
    }

    private function sumReportRowsMetric(Collection $rows, string $key): float
    {
        $total = $rows->sum(function (array $row) use ($key): float {
            $metric = $row[$key] ?? null;

            return is_numeric($metric) ? (float) $metric : 0.0;
        });

        return round((float) $total, 1);
    }

    private function formatMetricForTemplate(mixed $metric): string
    {
        if (!is_numeric($metric)) {
            return '';
        }

        return number_format((float) $metric, 1, '.', '');
    }

    private function formatTextForTemplate(mixed $value): string
    {
        $text = trim((string) $value);

        if ($text === '' || $text === 'N/A' || $text === '-' || $text === "\u{2014}") {
            return '';
        }

        return $text;
    }

    private function resolveRequestDate(DailyDriversTripTicket $ticket): ?Carbon
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $requestDate = $snapshot['request_date'] ?? $snapshot['requestDate'] ?? null;
        $parsed = $this->parseDateValue($requestDate);

        if ($parsed) {
            return $parsed;
        }

        return $this->parseDateValue($ticket->transportationRequestForm?->request_date);
    }

    private function resolveDurationHours(DailyDriversTripTicket $ticket): ?float
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $fromValue = $snapshot['date_time_from'] ?? $snapshot['dateTimeFrom'] ?? null;
        $toValue = $snapshot['date_time_to'] ?? $snapshot['dateTimeTo'] ?? null;

        $from = $this->parseDateValue($fromValue) ?? $this->parseDateValue($ticket->transportationRequestForm?->date_time_from);
        $to = $this->parseDateValue($toValue) ?? $this->parseDateValue($ticket->transportationRequestForm?->date_time_to);

        if (!$from || !$to) {
            return null;
        }

        if (!$to->greaterThan($from)) {
            return null;
        }

        return round($from->floatDiffInHours($to), 1);
    }

    private function resolveDistanceMetric(DailyDriversTripTicket $ticket): ?float
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        $distance = $this->toNullableFloat($ticket->distance_travelled)
            ?? $this->readNumericFromArray($snapshot, ['distance_travelled', 'distanceTravelled', 'distance']);

        if ($distance !== null) {
            return $distance;
        }

        $odometerStart = $this->toNullableFloat($ticket->odometer_start)
            ?? $this->readNumericFromArray($snapshot, ['odometer_start', 'odometerStart']);
        $odometerEnd = $this->toNullableFloat($ticket->odometer_end)
            ?? $this->readNumericFromArray($snapshot, ['odometer_end', 'odometerEnd']);

        if ($odometerStart !== null && $odometerEnd !== null) {
            return max(0.0, round($odometerEnd - $odometerStart, 2));
        }

        return $this->resolveDurationHours($ticket);
    }

    private function resolveFuelLitersByKind(DailyDriversTripTicket $ticket, string $fuelKind): ?float
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        $explicitFuel = $fuelKind === 'diesel'
            ? $this->readNumericFromArray($snapshot, ['diesel', 'diesel_liters', 'dieselFuelLiters', 'diesel_fuel_liters'])
            : $this->readNumericFromArray($snapshot, ['gasoline', 'gasoline_liters', 'gasolineFuelLiters', 'gasoline_fuel_liters']);

        if ($explicitFuel !== null) {
            return $explicitFuel;
        }

        $fuelTotal = $this->toNullableFloat($ticket->fuel_total);
        if ($fuelTotal === null) {
            $componentValues = [
                $this->toNullableFloat($ticket->fuel_issued_regional),
                $this->toNullableFloat($ticket->fuel_purchased_trip),
                $this->toNullableFloat($ticket->fuel_issued_nia),
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

        $inferredFuelKind = $this->inferFuelKind($ticket, $snapshot);
        if ($inferredFuelKind === null) {
            return $fuelKind === 'diesel' ? $fuelTotal : null;
        }

        return $inferredFuelKind === $fuelKind ? $fuelTotal : null;
    }

    private function inferFuelKind(DailyDriversTripTicket $ticket, array $snapshot): ?string
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

        $vehicleType = strtolower(trim((string) ($snapshot['vehicle_type'] ?? $snapshot['vehicleType'] ?? $ticket->transportationRequestForm?->vehicle_type ?? '')));
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

    private function resolveEngineOilLiters(DailyDriversTripTicket $ticket): ?float
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        return $this->toNullableFloat($ticket->engine_oil_liters)
            ?? $this->readNumericFromArray($snapshot, ['engine_oil_liters', 'engineOilLiters', 'engine_oil', 'engineOil']);
    }

    private function resolveGearOilLiters(DailyDriversTripTicket $ticket): ?float
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        return $this->toNullableFloat($ticket->gear_oil_liters)
            ?? $this->readNumericFromArray($snapshot, ['gear_oil_liters', 'gearOilLiters', 'gear_oil', 'gearOil']);
    }

    private function resolveBrakeFluidLiters(DailyDriversTripTicket $ticket): ?float
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        return $this->readNumericFromArray($snapshot, ['brake_fluid_liters', 'brakeFluidLiters', 'brake_fluid', 'brakeFluid', 'bf']);
    }

    private function resolveGreaseKilograms(DailyDriversTripTicket $ticket): ?float
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        return $this->toNullableFloat($ticket->grease_kgs)
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
            $tokens = preg_split('/\s*\/\s*|\s*,\s*|\s*;\s*|\R+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
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

    private function resolveTripDriverName(DailyDriversTripTicket $ticket): string
    {
        $driver = trim((string) ($ticket->assigned_driver_name ?? ''));
        if ($driver !== '') {
            return $driver;
        }

        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $driver = trim((string) ($snapshot['driver_name'] ?? $snapshot['driverName'] ?? ''));
        if ($driver !== '') {
            return $driver;
        }

        return trim((string) ($ticket->transportationRequestForm?->driver_name ?? ''));
    }

    private function resolveVehiclePlate(DailyDriversTripTicket $ticket): string
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $vehiclePlate = $this->readTextFromArray($snapshot, [
            'vehicle_plate_no',
            'vehicle_plate',
            'plate_no',
            'plateNo',
            'vehicle_id',
        ]);

        if ($vehiclePlate !== '') {
            return $vehiclePlate;
        }

        $vehiclePlate = trim((string) ($ticket->assigned_vehicle_code ?? ''));
        if ($vehiclePlate !== '') {
            return $vehiclePlate;
        }

        return trim((string) ($ticket->transportationRequestForm?->vehicle_id ?? ''));
    }

    private function resolvePropertyNumber(DailyDriversTripTicket $ticket): string
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $propertyNumber = $this->readTextFromArray($snapshot, [
            'property_number',
            'property_no',
            'propertyNo',
            'propertyNumber',
        ]);

        if ($propertyNumber !== '') {
            return $propertyNumber;
        }

        $vehicleCode = trim((string) ($ticket->assigned_vehicle_code ?? ''));
        $vehiclePlate = $this->resolveVehiclePlate($ticket);
        if ($vehicleCode !== '' && $vehicleCode !== $vehiclePlate) {
            return $vehicleCode;
        }

        return '';
    }

    private function resolveDieselPurchasedLiters(DailyDriversTripTicket $ticket): ?float
    {
        if ($this->shouldSkipDieselMetrics($ticket)) {
            return null;
        }

        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        return $this->toNullableFloat($ticket->fuel_purchased_trip)
            ?? $this->readNumericFromArray($snapshot, [
                'fuel_purchased_trip',
                'fuelPurchasedTrip',
                'diesel_purchased_trip',
                'dieselPurchasedTrip',
                'diesel_purchased',
                'dieselPurchased',
            ]);
    }

    private function resolveDieselIssuedLiters(DailyDriversTripTicket $ticket): ?float
    {
        if ($this->shouldSkipDieselMetrics($ticket)) {
            return null;
        }

        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        $issuedRegional = $this->toNullableFloat($ticket->fuel_issued_regional)
            ?? $this->readNumericFromArray($snapshot, ['fuel_issued_regional', 'fuelIssuedRegional']);
        $issuedNia = $this->toNullableFloat($ticket->fuel_issued_nia)
            ?? $this->readNumericFromArray($snapshot, ['fuel_issued_nia', 'fuelIssuedNia']);
        $explicitIssued = $this->readNumericFromArray($snapshot, ['diesel_issued', 'dieselIssued', 'fuel_issued', 'fuelIssued']);

        $total = 0.0;
        $hasValue = false;
        foreach ([$issuedRegional, $issuedNia, $explicitIssued] as $value) {
            if ($value === null) {
                continue;
            }

            $hasValue = true;
            $total += (float) $value;
        }

        return $hasValue ? $total : null;
    }

    private function resolveDieselConsumedLiters(DailyDriversTripTicket $ticket): ?float
    {
        if ($this->shouldSkipDieselMetrics($ticket)) {
            return null;
        }

        $distance = $this->resolveDistanceMetric($ticket);
        if ($distance === null) {
            return null;
        }

        return round(((float) $distance) / 10, 1);
    }

    private function resolveDieselBalanceAfterLiters(DailyDriversTripTicket $ticket): ?float
    {
        if ($this->shouldSkipDieselMetrics($ticket)) {
            return null;
        }

        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        $balanceBefore = $this->toNullableFloat($ticket->fuel_balance_before)
            ?? $this->readNumericFromArray($snapshot, [
                'fuel_balance_before',
                'fuelBalanceBefore',
                'diesel_balance_before',
                'dieselBalanceBefore',
                'balance_before',
                'balanceBefore',
            ]);

        $purchased = $this->resolveDieselPurchasedLiters($ticket);
        $issued = $this->resolveDieselIssuedLiters($ticket);
        $consumed = $this->resolveDieselConsumedLiters($ticket);

        if ($balanceBefore !== null || $purchased !== null || $issued !== null || $consumed !== null) {
            $computed = (float) ($balanceBefore ?? 0)
                + (float) ($purchased ?? 0)
                + (float) ($issued ?? 0)
                - (float) ($consumed ?? 0);

            return round($computed, 1);
        }

        return $this->toNullableFloat($ticket->fuel_balance_after)
            ?? $this->readNumericFromArray($snapshot, [
                'fuel_balance_after',
                'fuelBalanceAfter',
                'diesel_balance_after',
                'dieselBalanceAfter',
                'balance_after',
                'balanceAfter',
            ]);
    }

    private function shouldSkipDieselMetrics(DailyDriversTripTicket $ticket): bool
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $inferred = $this->inferFuelKind($ticket, $snapshot);

        return $inferred === 'gasoline';
    }

    private function resolveLatestMetric(Collection $items, callable $resolver): ?float
    {
        $latest = null;

        foreach ($items as $item) {
            $resolved = $resolver($item);
            if ($resolved === null) {
                continue;
            }

            $latest = (float) $resolved;
        }

        return $latest === null ? null : round($latest, 1);
    }

    private function resolveLatestRowMetric(Collection $rows, string $key): ?float
    {
        $latest = null;

        foreach ($rows as $row) {
            $value = $row[$key] ?? null;
            if (!is_numeric($value)) {
                continue;
            }

            $latest = (float) $value;
        }

        return $latest === null ? null : round($latest, 1);
    }

    private function readTextFromArray(array $source, array $keys): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $source)) {
                continue;
            }

            $value = trim((string) $source[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function resolvePassengerNames(DailyDriversTripTicket $ticket): array
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $passengerValue = $snapshot['business_passengers']
            ?? $snapshot['passengers']
            ?? $snapshot['passenger_names']
            ?? $snapshot['passenger']
            ?? [];

        if (is_string($passengerValue)) {
            $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $passengerValue, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } elseif (is_array($passengerValue)) {
            $tokens = $passengerValue;
        } else {
            return [];
        }

        return collect($tokens)
            ->map(function ($passenger): string {
                if (is_array($passenger)) {
                    return trim((string) ($passenger['name'] ?? $passenger['passenger'] ?? ''));
                }

                return trim((string) $passenger);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveDestination(DailyDriversTripTicket $ticket): string
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);

        return $this->readTextFromArray($snapshot, [
            'destination',
            'destination_place',
            'destinationPlace',
            'destinationLocation',
        ]);
    }

    private function resolveTripStatus(DailyDriversTripTicket $ticket): string
    {
        $snapshot = $this->decodeSnapshot($ticket->request_form_data);
        $status = trim((string) ($snapshot['status'] ?? ''));

        if ($status !== '') {
            return $status;
        }

        return trim((string) ($ticket->transportationRequestForm?->status ?? ''));
    }

    private function parseDateValue(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        try {
            return Carbon::parse($text);
        } catch (\Throwable) {
            return null;
        }
    }
}
