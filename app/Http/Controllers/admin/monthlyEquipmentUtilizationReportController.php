<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\AssignatoryPersonnel;
use App\Models\DailyDriversTripTicket;
use App\Support\AssignatoryPersonnelResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class monthlyEquipmentUtilizationReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'prepared_id' => ['nullable', 'integer'],
            'attested_id' => ['nullable', 'integer'],
            'approved_id' => ['nullable', 'integer'],
        ]);

        $selectedMonth = (string) ($validated['month'] ?? now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();
        $daysInMonth = (int) $monthStart->daysInMonth;

        $distanceMap = $this->buildDistanceMap($monthStart, $monthEnd);
        $vehicleRows = $this->buildVehicleRows($distanceMap, $daysInMonth);

        $assignatories = $this->loadAssignatories();
        $defaultAssignatory = AssignatoryPersonnelResolver::resolve();

        $preparedBy = $this->resolveSignatory($assignatories, $validated['prepared_id'] ?? null, $defaultAssignatory);
        $attestedBy = $this->resolveSignatory($assignatories, $validated['attested_id'] ?? null, $defaultAssignatory);
        $approvedBy = $this->resolveSignatory($assignatories, $validated['approved_id'] ?? null, $defaultAssignatory);

        return view('admin.monthly_equipment_utilization_report.monthly_ulitization_report_screen', [
            'selectedMonth' => $selectedMonth,
            'monthLabel' => strtoupper($monthStart->format('F Y')),
            'monthRangeLabel' => $monthStart->format('F j') . '-' . $monthEnd->format('j, Y'),
            'daysInMonth' => $daysInMonth,
            'vehicleRows' => $vehicleRows,
            'grandTotalDistance' => $vehicleRows->sum('totalDistance'),
            'grandTotalAmount' => $vehicleRows->sum(function ($row) {
                return $row['totalDistance'] * $row['rentalRate'];
            }),
            'assignatories' => $assignatories,
            'selectedPreparedId' => (int) ($validated['prepared_id'] ?? 0),
            'selectedAttestedId' => (int) ($validated['attested_id'] ?? 0),
            'selectedApprovedId' => (int) ($validated['approved_id'] ?? 0),
            'preparedBy' => $preparedBy,
            'attestedBy' => $attestedBy,
            'approvedBy' => $approvedBy,
        ]);
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'prepared_id' => ['nullable', 'integer'],
            'attested_id' => ['nullable', 'integer'],
            'approved_id' => ['nullable', 'integer'],
        ]);

        $selectedMonth = (string) ($validated['month'] ?? now()->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $monthEnd = (clone $monthStart)->endOfMonth();
        $daysInMonth = (int) $monthStart->daysInMonth;

        $distanceMap = $this->buildDistanceMap($monthStart, $monthEnd);
        $vehicleRows = $this->buildVehicleRows($distanceMap, $daysInMonth);

        $assignatories = $this->loadAssignatories();
        $defaultAssignatory = AssignatoryPersonnelResolver::resolve();

        $preparedBy = $this->resolveSignatory($assignatories, $validated['prepared_id'] ?? null, $defaultAssignatory);
        $attestedBy = $this->resolveSignatory($assignatories, $validated['attested_id'] ?? null, $defaultAssignatory);
        $approvedBy = $this->resolveSignatory($assignatories, $validated['approved_id'] ?? null, $defaultAssignatory);

        $templatePath = storage_path('app/public/forms/form09_rev08.xlsx');
        if (!is_readable($templatePath)) {
            abort(500, 'Utilization report template file not found: form09_rev08.xlsx');
        }

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(false);
        $reader->setIncludeCharts(false);
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $monthRangeLabel = strtoupper($monthStart->format('F j') . '-' . $monthEnd->format('j, Y'));
        $sheet->setCellValue('A13', 'Region 1 Urdaneta City,Pangasinan                                                                                                                                                    DATE:_' . $monthRangeLabel . '_');

        $monthRangeLabelShort = strtoupper($monthStart->format('F Y'));
        $sheet->setCellValue('D15', 'for the MONTH of ' . $monthRangeLabelShort);

        $startRow = 17;
        foreach ($vehicleRows as $index => $row) {
            $currentRow = $startRow + ($index * 3);
            if ($currentRow > 49) {
                break;
            }

            // Merge A, B, C vertically
            $sheet->mergeCells('A' . $currentRow . ':A' . ($currentRow + 2));
            $sheet->mergeCells('B' . $currentRow . ':B' . ($currentRow + 2));
            $sheet->mergeCells('C' . $currentRow . ':C' . ($currentRow + 2));

            $sheet->setCellValue('A' . $currentRow, $row['typeLabel']);
            $sheet->setCellValue('B' . $currentRow, 'N/A');
            $sheet->setCellValue('C' . $currentRow, $row['propPlateLabel']);
            
            $sheet->getStyle('A' . $currentRow . ':C' . ($currentRow + 2))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B' . $currentRow . ':C' . ($currentRow + 2))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            if ($row['totalDistance'] <= 0) {
                // Merge days 1 to 31 (D to AH) across all 3 rows for the vehicle block and write "NO OPERATION"
                $mergeRange = 'D' . $currentRow . ':AH' . ($currentRow + 2);
                $sheet->mergeCells($mergeRange);
                $sheet->setCellValue('D' . $currentRow, 'NO OPERATION');
                
                // Style the entire merged range for consistent centering and appearance
                $style = $sheet->getStyle($mergeRange);
                $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $style->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $style->getFont()->setBold(true);
            } else {
                $colAscii = ord('D');
                $colPrefix = '';
                for ($day = 1; $day <= 31; $day++) {
                    $colName = $colPrefix . chr($colAscii);
                    
                    // Merge each day column vertically
                    $sheet->mergeCells($colName . $currentRow . ':' . $colName . ($currentRow + 2));
                    
                    if ($day <= $daysInMonth) {
                        $distance = $row['days'][$day - 1] ?? 0;
                        $sheet->setCellValue($colName . $currentRow, (float) $distance);
                    } else {
                        $sheet->setCellValue($colName . $currentRow, 0);
                    }
                    
                    $sheet->getStyle($colName . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($colName . $currentRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $colAscii++;
                    if ($colAscii > ord('Z')) {
                        $colAscii = ord('A');
                        $colPrefix = 'A';
                    }
                }
            }
            
            // Merge AI, AJ, AK vertically
            $sheet->mergeCells('AI' . $currentRow . ':AI' . ($currentRow + 2));
            $sheet->mergeCells('AJ' . $currentRow . ':AJ' . ($currentRow + 2));
            $sheet->mergeCells('AK' . $currentRow . ':AK' . ($currentRow + 2));

            $sheet->setCellValue('AI' . $currentRow, (float) $row['totalDistance']);
            $sheet->setCellValue('AJ' . $currentRow, '₱' . number_format((float) $row['rentalRate'], 2). "/");
            $sheet->setCellValue('AK' . $currentRow, (float) ($row['totalDistance'] * $row['rentalRate']));
            
            $sheet->getStyle('AI' . $currentRow . ':AK' . ($currentRow + 2))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('AI' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Redundant clearing of rows 2 and 3 is removed because they are now part of vertical merges.
            // This prevents potential issues with some Excel readers when cells are both merged and explicitly cleared.
        }

        // Clear remaining template rows to prevent external link errors
        $lastRow = $startRow + (count($vehicleRows) * 3);
        for ($r = $lastRow; $r <= 49; $r++) {
            $sheet->getCell('A' . $r)->setValueExplicit(null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->getCell('B' . $r)->setValueExplicit(null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->getCell('C' . $r)->setValueExplicit(null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            
            $colAscii = ord('D');
            $colPrefix = '';
            for ($day = 1; $day <= 31; $day++) {
                $colName = $colPrefix . chr($colAscii);
                $sheet->getCell($colName . $r)->setValueExplicit(null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
                
                $colAscii++;
                if ($colAscii > ord('Z')) {
                    $colAscii = ord('A');
                    $colPrefix = 'A';
                }
            }
            $sheet->getCell('AI' . $r)->setValueExplicit(null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->getCell('AJ' . $r)->setValueExplicit(null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            $sheet->getCell('AK' . $r)->setValueExplicit(null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
        }

        $sheet->setCellValue('B56', strtoupper($preparedBy['name']));
        $sheet->setCellValue('B57', $preparedBy['position']);

        $sheet->setCellValue('N56', strtoupper($attestedBy['name']));
        $sheet->setCellValue('N57', $attestedBy['position']);

        $sheet->setCellValue('AC56', strtoupper($approvedBy['name']));
        $sheet->setCellValue('AC57', $approvedBy['position']);

        $fileName = 'Monthly_Equipment_Utilization_Report_' . $selectedMonth . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildDistanceMap(Carbon $monthStart, Carbon $monthEnd): array
    {
        $tickets = DailyDriversTripTicket::query()
            ->with(['transportationRequestForm:id,request_date,vehicle_id'])
            ->whereHas('transportationRequestForm', function ($query) use ($monthStart, $monthEnd) {
                $query->whereDate('request_date', '>=', $monthStart->toDateString())
                    ->whereDate('request_date', '<=', $monthEnd->toDateString());
            })
            ->get();

        $distanceMap = [];

        foreach ($tickets as $ticket) {
            $vehicleCode = $this->resolveVehicleCode($ticket);
            if ($vehicleCode === '') {
                continue;
            }

            $requestDate = $this->resolveRequestDate($ticket);
            if (!$requestDate) {
                continue;
            }

            if ($requestDate->lt($monthStart) || $requestDate->gt($monthEnd)) {
                continue;
            }

            $distance = $this->resolveDistance($ticket);
            if ($distance === null) {
                continue;
            }

            $day = (int) $requestDate->day;

            if (!isset($distanceMap[$vehicleCode])) {
                $distanceMap[$vehicleCode] = [];
            }

            $distanceMap[$vehicleCode][$day] = ($distanceMap[$vehicleCode][$day] ?? 0) + $distance;
        }

        return $distanceMap;
    }

    private function buildVehicleRows(array $distanceMap, int $daysInMonth): Collection
    {
        $vehicles = AdminVehicleAvailability::query()
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_code')
            ->get();

        $rows = $vehicles->map(function (AdminVehicleAvailability $vehicle) use ($distanceMap, $daysInMonth) {
            $vehicleCode = trim((string) ($vehicle->vehicle_code ?? ''));
            $vehicleType = trim((string) ($vehicle->vehicle_type ?? ''));
            $capacityLabel = trim((string) ($vehicle->capacity_label ?? ''));

            $typeLabel = $vehicleType !== '' ? $vehicleType : 'N/A';
            if ($capacityLabel !== '') {
                $typeLabel .= ' (' . $capacityLabel . ')';
            }

            $days = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $days[] = (float) ($distanceMap[$vehicleCode][$day] ?? 0);
            }

            return [
                'vehicleCode' => $vehicleCode,
                'typeLabel' => $typeLabel,
                'serialLabel' => 'N/A',
                'propPlateLabel' => $vehicleCode !== '' ? $vehicleCode : 'N/A',
                'days' => $days,
                'totalDistance' => array_sum($days),
                'rentalRate' => (float) ($vehicle->rental_rate ?? 0),
            ];
        });

        $knownCodes = $rows->pluck('vehicleCode')->filter()->all();

        foreach (array_keys($distanceMap) as $vehicleCode) {
            if (!in_array($vehicleCode, $knownCodes, true)) {
                $days = [];
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $days[] = (float) ($distanceMap[$vehicleCode][$day] ?? 0);
                }

                $rows->push([
                    'vehicleCode' => $vehicleCode,
                    'typeLabel' => 'Unregistered',
                    'serialLabel' => 'N/A',
                    'propPlateLabel' => $vehicleCode,
                    'days' => $days,
                    'totalDistance' => array_sum($days),
                    'rentalRate' => 0.0,
                ]);
            }
        }

        return $rows
            ->sortBy(function (array $row) {
                return strtolower($row['typeLabel'] . '|' . $row['propPlateLabel']);
            })
            ->values();
    }

    private function resolveRequestDate(DailyDriversTripTicket $ticket): ?Carbon
    {
        $requestDate = $ticket->transportationRequestForm?->request_date;
        if ($requestDate) {
            return Carbon::parse($requestDate);
        }

        $snapshotDate = trim((string) data_get($ticket->request_form_data, 'request_date', ''));
        if ($snapshotDate !== '') {
            try {
                return Carbon::parse($snapshotDate);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function resolveVehicleCode(DailyDriversTripTicket $ticket): string
    {
        $vehicleCode = trim((string) ($ticket->assigned_vehicle_code ?? ''));
        if ($vehicleCode !== '') {
            return $vehicleCode;
        }

        $vehicleId = data_get($ticket->request_form_data, 'vehicle_id');
        if ($vehicleId === null) {
            $vehicleId = $ticket->transportationRequestForm?->vehicle_id;
        }

        $vehicleCodes = $this->extractVehicleCodes((string) $vehicleId);
        if (count($vehicleCodes) === 1) {
            return $vehicleCodes[0];
        }

        return '';
    }

    private function resolveDistance(DailyDriversTripTicket $ticket): ?float
    {
        $distance = $this->toNullableFloat($ticket->distance_travelled);
        if ($distance !== null) {
            return $distance;
        }

        $odometerStart = $this->toNullableFloat($ticket->odometer_start);
        $odometerEnd = $this->toNullableFloat($ticket->odometer_end);

        if ($odometerStart !== null && $odometerEnd !== null) {
            return max(0.0, round($odometerEnd - $odometerStart, 2));
        }

        return null;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function extractVehicleCodes(string $vehicleIds): array
    {
        $value = trim($vehicleIds);
        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $tokens = $decoded;
        } else {
            $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return collect($tokens)
            ->map(function ($token) {
                if (is_array($token)) {
                    return trim((string) ($token['vehicle_code'] ?? $token['code'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $code) {
                return $code !== '';
            })
            ->unique()
            ->values()
            ->all();
    }

    private function loadAssignatories(): Collection
    {
        if (!Schema::hasTable('assignatory_personnel')) {
            return collect();
        }

        $query = AssignatoryPersonnel::query();

        if (Schema::hasColumn('assignatory_personnel', 'is_active')) {
            $query->orderByDesc('is_active');
        }

        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get(['id', 'name', 'position', 'is_active']);
    }

    private function resolveSignatory(Collection $assignatories, ?int $assignatoryId, array $fallback): array
    {
        if ($assignatoryId) {
            $target = $assignatories->firstWhere('id', $assignatoryId);
            if ($target) {
                $name = trim((string) ($target->name ?? ''));
                $position = trim((string) ($target->position ?? ''));

                return [
                    'name' => $name !== '' ? $name : (string) ($fallback['name'] ?? 'N/A'),
                    'position' => $position !== '' ? $position : (string) ($fallback['position'] ?? 'N/A'),
                ];
            }
        }

        return [
            'name' => (string) ($fallback['name'] ?? 'N/A'),
            'position' => (string) ($fallback['position'] ?? 'N/A'),
        ];
    }
}
