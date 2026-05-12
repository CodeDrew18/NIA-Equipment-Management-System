<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\DailyDriversTripTicket;
use App\Models\DriverPerformanceEvaluation;
use App\Models\FuelIssuance;
use App\Models\TransportationRequestFormModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class travelReportController extends Controller
{
    public function index(Request $request)
    {
        [$fromDate, $toDate, $selectedStatus, $selectedSort, $selectedMonth] = $this->resolveFilters($request);

        $this->syncMissingPerformanceEvaluations($fromDate, $toDate, $selectedStatus);

        $baseQuery = $this->buildFilteredRequestsQuery($fromDate, $toDate, $selectedStatus);
        $metricsQuery = $this->buildFilteredRequestsQuery($fromDate, $toDate, $selectedStatus);

        if ($selectedSort === 'date_asc') {
            $baseQuery->orderBy('request_date')->orderBy('id');
        } else {
            $baseQuery->orderByDesc('request_date')->orderByDesc('id');
        }

        $reportRequests = $baseQuery->paginate(10)->withQueryString();

        $reportRequests->getCollection()->transform(function (TransportationRequestFormModel $requestItem) {
            $requestItem->setAttribute('attachment_links', $this->formatAttachmentLinks(
                $this->buildTransportationRequestAttachmentLinks($requestItem)
            ));

            return $requestItem;
        });

        $activeRequests = (clone $metricsQuery)->count();
        $activeTripTickets = $this->countTripTickets($fromDate, $toDate, $selectedStatus, false);
        $inTransitTrips = $this->countTripTickets($fromDate, $toDate, $selectedStatus, true);
        $totalFuelReleased = $this->sumFuelReleasedLiters($fromDate, $toDate, $selectedStatus);
        $tripTicketsRows = $this->buildTripTicketRows($fromDate, $toDate, $selectedStatus, $selectedSort);
        $fuelIssuanceRows = $this->buildFuelIssuanceRows($fromDate, $toDate, $selectedStatus, $selectedSort);
        $performanceEvaluationRows = $this->buildPerformanceEvaluationRows($fromDate, $toDate, $selectedStatus, $selectedSort);

        $topDrivers = $this->buildTopDrivers($fromDate, $toDate, $selectedStatus);
        $averageEvaluationRating = $this->averagePerformanceRating($fromDate, $toDate, $selectedStatus);
        $driverPerformanceScore = $averageEvaluationRating ?? (float) ($topDrivers[0]['score'] ?? 0.0);

        $chartData = $this->buildChartData(
            (clone $this->buildFilteredRequestsQuery($fromDate, $toDate, $selectedStatus))
                ->get([
                    'id',
                    'request_date',
                    'date_time_from',
                    'date_time_to',
                ])
        );

        $statusOptions = TransportationRequestFormModel::query()
            ->whereDate('request_date', '>=', $fromDate)
            ->whereDate('request_date', '<=', $toDate)
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->values();

        return view('admin.travel_reports.travel_report', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'selectedMonth' => $selectedMonth,
            'dateRangeLabel' => Carbon::parse($fromDate)->format('M d, Y') . ' - ' . Carbon::parse($toDate)->format('M d, Y'),
            'selectedStatus' => $selectedStatus,
            'selectedSort' => $selectedSort,
            'statusOptions' => $statusOptions,
            'reportRequests' => $reportRequests,
            'activeRequests' => $activeRequests,
            'totalFuelReleased' => round($totalFuelReleased, 1),
            'activeTripTickets' => $activeTripTickets,
            'inTransitTrips' => $inTransitTrips,
            'driverPerformanceScore' => round($driverPerformanceScore, 2),
            'driverPerformanceText' => $averageEvaluationRating !== null
                ? 'Average evaluated score'
                : 'No submitted evaluations in range',
            'topDrivers' => $topDrivers,
            'chartBars' => $chartData['bars'],
            'chartLabels' => $chartData['labels'],
            'tripTicketsRows' => $tripTicketsRows,
            'fuelIssuanceRows' => $fuelIssuanceRows,
            'performanceEvaluationRows' => $performanceEvaluationRows,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$fromDate, $toDate, $selectedStatus, $selectedSort, $selectedMonth] = $this->resolveFilters($request);

        $query = $this->buildFilteredRequestsQuery($fromDate, $toDate, $selectedStatus);

        if ($selectedSort === 'date_asc') {
            $query->orderBy('request_date')->orderBy('id');
        } else {
            $query->orderByDesc('request_date')->orderByDesc('id');
        }

        $rows = $query->get([
            'id',
            'form_id',
            'request_date',
            'requested_by',
            'destination',
            'date_time_from',
            'date_time_to',
            'vehicle_type',
            'vehicle_id',
            'status',
            'attachments',
        ]);

        $activeRequests = $rows->count();
        $activeTripTickets = $this->countTripTickets($fromDate, $toDate, $selectedStatus, false);
        $inTransitTrips = $this->countTripTickets($fromDate, $toDate, $selectedStatus, true);
        $totalFuelReleased = $this->sumFuelReleasedLiters($fromDate, $toDate, $selectedStatus);
        $topDrivers = $this->buildTopDrivers($fromDate, $toDate, $selectedStatus);
        $averageEvaluationRating = $this->averagePerformanceRating($fromDate, $toDate, $selectedStatus);
        $driverPerformanceScore = $averageEvaluationRating ?? (float) ($topDrivers[0]['score'] ?? 0.0);
        $statusLabel = strtolower($selectedStatus) === 'all' ? 'All Statuses' : $selectedStatus;

        $spreadsheet = new Spreadsheet();
        $reportSheet = $spreadsheet->getActiveSheet();
        $reportSheet->setTitle('Travel Report');

        $this->buildTravelReportExportSheet(
            $reportSheet,
            $rows,
            $fromDate,
            $toDate,
            $statusLabel
        );

        $analyticsSheet = new Worksheet($spreadsheet, 'Analytics');
        $spreadsheet->addSheet($analyticsSheet);

        $this->buildTravelAnalyticsExportSheet(
            $analyticsSheet,
            $fromDate,
            $toDate,
            $statusLabel,
            $activeRequests,
            $totalFuelReleased,
            $activeTripTickets,
            $inTransitTrips,
            $driverPerformanceScore,
            $topDrivers
        );

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'summary_travel_report_' . Carbon::parse($fromDate)->format('Ymd') . '_' . Carbon::parse($toDate)->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function buildTravelReportExportSheet(
        Worksheet $sheet,
        Collection $rows,
        string $fromDate,
        string $toDate,
        string $statusLabel
    ): void {
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'NIA Travel Report - Full Export');
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Date Range: ' . Carbon::parse($fromDate)->format('M d, Y') . ' to ' . Carbon::parse($toDate)->format('M d, Y'));
        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'Status Filter: ' . $statusLabel . ' | Generated: ' . now()->format('M d, Y h:i A'));

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF003466'],
            ],
        ]);

        $sheet->getStyle('A2:I3')->applyFromArray([
            'font' => [
                'size' => 10,
                'color' => ['argb' => 'FF424750'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFF2F4F7'],
            ],
        ]);

        $sheet->fromArray([
            'Request ID',
            'Request Date',
            'Requester',
            'Destination',
            'Schedule From',
            'Schedule To',
            'Equipment',
            'Status',
            'Attachments',
        ], null, 'A6');

        $sheet->getStyle('A6:I6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF003466'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFE6EEF8'],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(6)->setRowHeight(22);

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(34);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(24);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(14);

        $rowPointer = 7;
        foreach ($rows as $index => $row) {
            $requestAttachments = $this->buildTransportationRequestAttachmentLinks($row);

            $sheet->fromArray([
                (string) ($row->form_id ?: 'N/A'),
                optional($row->request_date)->format('M d, Y') ?: 'N/A',
                (string) ($row->requestor_name ?: $row->requested_by ?: 'N/A'),
                (string) ($row->destination ?: 'N/A'),
                optional($row->date_time_from)->format('M d, Y h:i A') ?: 'N/A',
                optional($row->date_time_to)->format('M d, Y h:i A') ?: 'N/A',
                (string) ($row->vehicle_id ?: $row->vehicle_type ?: 'N/A'),
                (string) ($row->status ?: 'N/A'),
                $requestAttachments->count() > 0
                    ? $requestAttachments->count() . ' file(s)'
                    : 'No attachment',
            ], null, 'A' . $rowPointer);

            if ($index % 2 === 1) {
                $sheet->getStyle('A' . $rowPointer . ':I' . $rowPointer)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFF8FAFD'],
                    ],
                ]);
            }

            $rowPointer++;
        }

        if ($rows->isEmpty()) {
            $sheet->mergeCells('A7:I7');
            $sheet->setCellValue('A7', 'No travel records found for the selected date range and filters.');
            $sheet->getStyle('A7')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FF737781'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $rowPointer = 8;
        }

        $lastDataRow = max(7, $rowPointer - 1);

        $sheet->getStyle('A6:I' . $lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD9DDE5'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('D7:D' . $lastDataRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A7:C' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('E7:I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setAutoFilter('A6:I6');
        $sheet->freezePane('A7');
    }

    private function buildTravelAnalyticsExportSheet(
        Worksheet $sheet,
        string $fromDate,
        string $toDate,
        string $statusLabel,
        int $activeRequests,
        float $totalFuelReleased,
        int $activeTripTickets,
        int $inTransitTrips,
        float $driverPerformanceScore,
        array $topDrivers
    ): void {
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Travel Report Analytics Summary');
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Date Range: ' . Carbon::parse($fromDate)->format('M d, Y') . ' to ' . Carbon::parse($toDate)->format('M d, Y'));
        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'Status Filter: ' . $statusLabel);

        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF003466'],
            ],
        ]);

        $sheet->getStyle('A2:E3')->applyFromArray([
            'font' => [
                'size' => 10,
                'color' => ['argb' => 'FF424750'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFF2F4F7'],
            ],
        ]);

        $sheet->fromArray(['Metric', 'Value'], null, 'A5');
        $sheet->mergeCells('B5:E5');

        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF003466'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFE6EEF8'],
            ],
        ]);

        $metrics = [
            ['Active Requests', number_format($activeRequests)],
            ['Total Fuel Released (L)', number_format($totalFuelReleased, 1)],
            ['Active Trip Tickets', number_format($activeTripTickets)],
            ['In-Transit Trips', number_format($inTransitTrips)],
            ['Driver Performance Score', number_format($driverPerformanceScore, 2)],
        ];

        $metricRow = 6;
        foreach ($metrics as [$metricLabel, $metricValue]) {
            $sheet->setCellValue('A' . $metricRow, $metricLabel);
            $sheet->setCellValue('B' . $metricRow, $metricValue);
            $sheet->mergeCells('B' . $metricRow . ':E' . $metricRow);
            $metricRow++;
        }

        $sheet->setCellValue('A' . ($metricRow + 1), 'Top Drivers');
        $sheet->mergeCells('A' . ($metricRow + 1) . ':E' . ($metricRow + 1));
        $sheet->getStyle('A' . ($metricRow + 1) . ':E' . ($metricRow + 1))->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF003466'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFE6EEF8'],
            ],
        ]);

        $driverHeaderRow = $metricRow + 2;
        $sheet->fromArray(['Rank', 'Driver', 'Score', 'Trips', 'Badge'], null, 'A' . $driverHeaderRow);
        $sheet->getStyle('A' . $driverHeaderRow . ':E' . $driverHeaderRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF003466'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFF2F4F7'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $driverRow = $driverHeaderRow + 1;
        if (empty($topDrivers)) {
            $sheet->mergeCells('A' . $driverRow . ':E' . $driverRow);
            $sheet->setCellValue('A' . $driverRow, 'No ranked drivers available in selected range.');
            $sheet->getStyle('A' . $driverRow)->applyFromArray([
                'font' => [
                    'italic' => true,
                    'color' => ['argb' => 'FF737781'],
                ],
            ]);
        } else {
            foreach ($topDrivers as $index => $driver) {
                $sheet->fromArray([
                    '#' . ($index + 1),
                    (string) ($driver['name'] ?? 'N/A'),
                    (string) ($driver['scoreLabel'] ?? '0.00/5.0'),
                    (string) ($driver['trips'] ?? 0),
                    (string) ($driver['badge'] ?? 'ACTIVE'),
                ], null, 'A' . $driverRow);

                if ($index % 2 === 1) {
                    $sheet->getStyle('A' . $driverRow . ':E' . $driverRow)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['argb' => 'FFF8FAFD'],
                        ],
                    ]);
                }

                $driverRow++;
            }
        }

        $lastRow = max($driverHeaderRow + 1, $driverRow);

        $sheet->getStyle('A5:E' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD9DDE5'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(14);

        $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B6:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A6');
    }

    private function resolveFilters(Request $request): array
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'in:date_desc,date_asc'],
        ]);

        $fromInput = trim((string) ($validated['from'] ?? ''));
        $toInput = trim((string) ($validated['to'] ?? ''));
        $hasCustomRange = $fromInput !== '' || $toInput !== '';

        if ($hasCustomRange) {
            if ($fromInput === '' && $toInput !== '') {
                $fromInput = $toInput;
            }

            if ($toInput === '' && $fromInput !== '') {
                $toInput = $fromInput;
            }

            $fromDate = Carbon::parse($fromInput)->toDateString();
            $toDate = Carbon::parse($toInput)->toDateString();
            $selectedMonth = Carbon::parse($fromDate)->format('Y-m');
        } else {
            $selectedMonth = trim((string) ($validated['month'] ?? ''));
            if ($selectedMonth === '') {
                $selectedMonth = now()->format('Y-m');
            }

            $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
            $fromDate = $monthDate->copy()->startOfMonth()->toDateString();
            $toDate = $monthDate->copy()->endOfMonth()->toDateString();
        }

        $selectedStatus = trim((string) ($validated['status'] ?? 'all'));
        if ($selectedStatus === '') {
            $selectedStatus = 'all';
        }

        $selectedSort = (string) ($validated['sort'] ?? 'date_desc');

        return [$fromDate, $toDate, $selectedStatus, $selectedSort, $selectedMonth];
    }

    private function buildFilteredRequestsQuery(string $fromDate, string $toDate, string $selectedStatus): Builder
    {
        $query = TransportationRequestFormModel::query()
            ->with([
                'dailyDriversTripTicket:id,transportation_request_form_id,distance_travelled,odometer_start,odometer_end',
            ])
            ->whereDate('request_date', '>=', $fromDate)
            ->whereDate('request_date', '<=', $toDate);

        $this->applyStatusFilter($query, $selectedStatus);

        return $query;
    }

    private function applyStatusFilter(Builder $query, string $selectedStatus): void
    {
        if (strtolower($selectedStatus) === 'all') {
            return;
        }

        $query->where('status', $selectedStatus);
    }

    private function countTripTickets(string $fromDate, string $toDate, string $selectedStatus, bool $onlyInTransit): int
    {
        return DailyDriversTripTicket::query()
            ->whereHas('transportationRequestForm', function (Builder $query) use ($fromDate, $toDate, $selectedStatus, $onlyInTransit) {
                $query->whereDate('request_date', '>=', $fromDate)
                    ->whereDate('request_date', '<=', $toDate)
                    ->when($onlyInTransit, function (Builder $nested) {
                        $nested->where('status', 'On Trip');
                    });

                $this->applyStatusFilter($query, $selectedStatus);
            })
            ->count();
    }

    private function sumFuelReleasedLiters(string $fromDate, string $toDate, string $selectedStatus): float
    {
        $total = FuelIssuance::query()
            ->whereHas('transportationRequestForm', function (Builder $query) use ($fromDate, $toDate, $selectedStatus) {
                $query->whereDate('request_date', '>=', $fromDate)
                    ->whereDate('request_date', '<=', $toDate);

                $this->applyStatusFilter($query, $selectedStatus);
            })
            ->selectRaw('COALESCE(SUM(gasoline_quantity + diesel_quantity + fuel_save_quantity + v_power_quantity), 0) as total_liters')
            ->value('total_liters');

        return (float) ($total ?? 0.0);
    }

    private function buildTripTicketRows(string $fromDate, string $toDate, string $selectedStatus, string $selectedSort): Collection
    {
        $rows = DailyDriversTripTicket::query()
            ->with([
                'transportationRequestForm:id,form_id,request_date,date_time_from,date_time_to,destination,vehicle_id,driver_name,status,attachments',
            ])
            ->whereHas('transportationRequestForm', function (Builder $query) use ($fromDate, $toDate, $selectedStatus) {
                $query->whereDate('request_date', '>=', $fromDate)
                    ->whereDate('request_date', '<=', $toDate);

                $this->applyStatusFilter($query, $selectedStatus);
            })
            ->get();

        $rows = $this->sortByRequestDate($rows, $selectedSort, function (DailyDriversTripTicket $ticket) {
            return $ticket->transportationRequestForm?->request_date;
        });

        return $rows
            ->take(20)
            ->map(function (DailyDriversTripTicket $ticket): array {
                $request = $ticket->transportationRequestForm;

                return [
                    'formId' => (string) ($request?->form_id ?: 'N/A'),
                    'requestDate' => optional($request?->request_date)->format('M d, Y') ?: 'N/A',
                    'driverName' => (string) ($request?->driver_name ?: 'N/A'),
                    'vehicleId' => (string) ($request?->vehicle_id ?: 'N/A'),
                    'destination' => (string) ($request?->destination ?: 'N/A'),
                    'distance' => is_numeric($ticket->distance_travelled)
                        ? number_format((float) $ticket->distance_travelled, 1)
                        : 'N/A',
                    'fuelTotal' => is_numeric($ticket->fuel_total)
                        ? number_format((float) $ticket->fuel_total, 1)
                        : 'N/A',
                    'attachments' => $this->formatAttachmentLinks(
                        $this->buildTripTicketAttachmentLinks($ticket)
                    ),
                    'status' => (string) ($request?->status ?: 'N/A'),
                ];
            })
            ->values();
    }

    private function buildFuelIssuanceRows(string $fromDate, string $toDate, string $selectedStatus, string $selectedSort): Collection
    {
        $rows = FuelIssuance::query()
            ->with([
                'transportationRequestForm:id,form_id,request_date,status,destination,driver_name,attachments',
            ])
            ->whereHas('transportationRequestForm', function (Builder $query) use ($fromDate, $toDate, $selectedStatus) {
                $query->whereDate('request_date', '>=', $fromDate)
                    ->whereDate('request_date', '<=', $toDate);

                $this->applyStatusFilter($query, $selectedStatus);
            })
            ->get();

        $rows = $this->sortByRequestDate($rows, $selectedSort, function (FuelIssuance $issuance) {
            return $issuance->transportationRequestForm?->request_date;
        });

        return $rows
            ->take(20)
            ->map(function (FuelIssuance $issuance): array {
                $request = $issuance->transportationRequestForm;
                $liters = (float) $issuance->gasoline_quantity
                    + (float) $issuance->diesel_quantity
                    + (float) $issuance->fuel_save_quantity
                    + (float) $issuance->v_power_quantity;

                return [
                    'ctrlNumber' => (string) ($issuance->ctrl_number ?: 'N/A'),
                    'formId' => (string) ($request?->form_id ?: 'N/A'),
                    'requestDate' => optional($request?->request_date)->format('M d, Y') ?: 'N/A',
                    'driverName' => (string) ($issuance->driver_name ?: $request?->driver_name ?: 'N/A'),
                    'dealer' => (string) ($issuance->dealer ?: 'N/A'),
                    'liters' => number_format($liters, 1),
                    'totalAmount' => number_format((float) $issuance->total_amount, 2),
                    'dispatchedAt' => optional($issuance->dispatched_at)->format('M d, Y h:i A') ?: 'N/A',
                    'attachments' => $this->formatAttachmentLinks(
                        $this->buildFuelIssuanceAttachmentLinks($issuance)
                    ),
                    'status' => (string) ($request?->status ?: 'N/A'),
                ];
            })
            ->values();
    }

    private function buildPerformanceEvaluationRows(string $fromDate, string $toDate, string $selectedStatus, string $selectedSort): Collection
    {
        $rows = DriverPerformanceEvaluation::query()
            ->with([
                'transportationRequestForm:id,form_id,request_date,status,destination,driver_name,attachments',
            ])
            ->whereHas('transportationRequestForm', function (Builder $query) use ($fromDate, $toDate, $selectedStatus) {
                $query->whereDate('request_date', '>=', $fromDate)
                    ->whereDate('request_date', '<=', $toDate);

                $this->applyStatusFilter($query, $selectedStatus);
            })
            ->get();

        $rows = $this->sortByRequestDate($rows, $selectedSort, function (DriverPerformanceEvaluation $evaluation) {
            return $evaluation->transportationRequestForm?->request_date;
        });

        return $rows
            ->take(20)
            ->map(function (DriverPerformanceEvaluation $evaluation): array {
                $request = $evaluation->transportationRequestForm;
                $rating = is_numeric($evaluation->overall_rating)
                    ? number_format((float) $evaluation->overall_rating, 2)
                    : null;

                return [
                    'formId' => (string) ($request?->form_id ?: 'N/A'),
                    'requestDate' => optional($request?->request_date)->format('M d, Y') ?: 'N/A',
                    'driverName' => (string) ($evaluation->driver_name ?: $request?->driver_name ?: 'N/A'),
                    'evaluationStatus' => (string) ($evaluation->status ?: 'Pending'),
                    'overallRating' => $rating,
                    'evaluatedAt' => optional($evaluation->evaluated_at)->format('M d, Y h:i A') ?: 'Not yet evaluated',
                    'attachments' => $this->formatAttachmentLinks(
                        $this->buildPerformanceEvaluationAttachmentLinks($evaluation)
                    ),
                    'requestStatus' => (string) ($request?->status ?: 'N/A'),
                ];
            })
            ->values();
    }

    private function buildPerformanceEvaluationAttachmentLinks(DriverPerformanceEvaluation $evaluation): Collection
    {
        $request = $evaluation->transportationRequestForm;
        $targetCopyKey = strtolower(trim((string) ($evaluation->copy_key ?? '')));

        $links = $this->buildRequestAttachmentLinks($request, function (array $attachment) use ($targetCopyKey): bool {
            $process = strtolower(trim((string) ($attachment['process'] ?? '')));
            $copyKey = strtolower(trim((string) ($attachment['copy_key'] ?? '')));

            if ($process === 'driver_performance_evaluation') {
                return $targetCopyKey === '' || $copyKey === '' || $copyKey === $targetCopyKey;
            }

            if ($process !== '') {
                return false;
            }

            $fileName = strtolower(trim((string) ($attachment['file_name'] ?? '')));

            return str_starts_with($fileName, 'driver_performance_evaluation_')
                || str_starts_with($fileName, 'dpe_form15_');
        });

        $this->appendDirectAttachmentLink($links, $evaluation->attachment);

        return $links;
    }

    private function buildTransportationRequestAttachmentLinks(?TransportationRequestFormModel $request): Collection
    {
        return $this->buildRequestAttachmentLinks($request, function (array $attachment): bool {
            $process = strtolower(trim((string) ($attachment['process'] ?? '')));
            if ($process === 'transportation_request_form') {
                return true;
            }

            if ($process !== '') {
                return false;
            }

            $fileName = strtolower(trim((string) ($attachment['file_name'] ?? '')));

            return str_starts_with($fileName, 'transportation_request_form_');
        });
    }

    private function buildTripTicketAttachmentLinks(DailyDriversTripTicket $ticket): Collection
    {
        $request = $ticket->transportationRequestForm;

        $links = $this->buildRequestAttachmentLinks($request, function (array $attachment): bool {
            $process = strtolower(trim((string) ($attachment['process'] ?? '')));
            if ($process === 'daily_drivers_trip_ticket') {
                return true;
            }

            if ($process !== '') {
                return false;
            }

            $fileName = strtolower(trim((string) ($attachment['file_name'] ?? '')));

            return str_starts_with($fileName, 'dtt_');
        });

        $this->appendDirectAttachmentLink($links, $ticket->attachment);

        return $links;
    }

    private function buildFuelIssuanceAttachmentLinks(FuelIssuance $issuance): Collection
    {
        $request = $issuance->transportationRequestForm;
        $targetCopyKey = strtolower(trim((string) ($issuance->copy_key ?? '')));

        $links = $this->buildRequestAttachmentLinks($request, function (array $attachment) use ($targetCopyKey): bool {
            $process = strtolower(trim((string) ($attachment['process'] ?? '')));
            $copyKey = strtolower(trim((string) ($attachment['copy_key'] ?? '')));

            if ($process === 'fuel_issuance') {
                return $targetCopyKey === '' || $copyKey === '' || $copyKey === $targetCopyKey;
            }

            if ($process !== '') {
                return false;
            }

            $fileName = strtolower(trim((string) ($attachment['file_name'] ?? '')));

            return str_starts_with($fileName, 'fuel_issuance_');
        });

        $this->appendDirectAttachmentLink($links, $issuance->attachment);

        if ($links->isEmpty()) {
            $this->appendFuelIssuanceFallbackAttachmentLink($links, $issuance);
        }

        return $links;
    }

    private function appendFuelIssuanceFallbackAttachmentLink(Collection $links, FuelIssuance $issuance): void
    {
        $issuanceId = (int) ($issuance->id ?? 0);
        if ($issuanceId <= 0) {
            return;
        }

        $fallbackName = trim((string) data_get($issuance->attachment, 'file_name', ''));
        if ($fallbackName === '') {
            $ctrlNumber = trim((string) ($issuance->ctrl_number ?? ''));
            $safeCtrlNumber = preg_replace('/[^A-Za-z0-9._-]/', '_', $ctrlNumber ?: 'FIS');
            $fallbackName = 'Fuel_Issuance_' . $safeCtrlNumber . '.xlsx';
        }

        $this->appendAttachmentLink($links, [
            'name' => $fallbackName,
            'url' => route('admin.fuel_issuance_slip.attachment.download', [
                'fuelIssuance' => $issuanceId,
            ]),
            'file_path' => '__fuel_issuance_record_' . $issuanceId,
        ]);
    }

    private function buildRequestAttachmentLinks(?TransportationRequestFormModel $request, ?callable $filter = null): Collection
    {
        $links = collect();

        if (!$request) {
            return $links;
        }

        $attachments = $request->normalizeAttachments();

        foreach ($attachments as $index => $attachment) {
            if (!is_array($attachment)) {
                continue;
            }

            if ($filter && !$filter($attachment)) {
                continue;
            }

            $filePath = trim((string) ($attachment['file_path'] ?? ''));
            if ($filePath === '' || !Storage::disk('public')->exists($filePath)) {
                continue;
            }

            $fileName = trim((string) ($attachment['file_name'] ?? basename($filePath)));

            $this->appendAttachmentLink($links, [
                'name' => $fileName !== '' ? $fileName : basename($filePath),
                'url' => route('admin.transportation-request.attachment.view', [
                    'transportationRequest' => $request->id,
                    'index' => $index,
                ]),
                'file_path' => $filePath,
            ]);
        }

        return $links;
    }

    private function appendDirectAttachmentLink(Collection $links, mixed $attachment): void
    {
        $filePath = '';
        $fileName = '';

        if (is_array($attachment)) {
            $filePath = trim((string) ($attachment['file_path'] ?? ''));
            $fileName = trim((string) ($attachment['file_name'] ?? basename($filePath)));
        } elseif (is_string($attachment)) {
            $filePath = trim($attachment);
            $fileName = basename($filePath);
        }

        if ($filePath === '' || !Storage::disk('public')->exists($filePath)) {
            return;
        }

        $this->appendAttachmentLink($links, [
            'name' => $fileName !== '' ? $fileName : basename($filePath),
            'url' => asset('storage/' . ltrim($filePath, '/')),
            'file_path' => $filePath,
        ]);
    }

    private function appendAttachmentLink(Collection $links, array $link): void
    {
        $filePath = trim((string) ($link['file_path'] ?? ''));
        if ($filePath === '') {
            return;
        }

        $alreadyExists = $links->contains(function (array $existingLink) use ($filePath): bool {
            return trim((string) ($existingLink['file_path'] ?? '')) === $filePath;
        });

        if ($alreadyExists) {
            return;
        }

        $links->push([
            'name' => (string) ($link['name'] ?? 'Attachment'),
            'url' => (string) ($link['url'] ?? '#'),
            'file_path' => $filePath,
        ]);
    }

    private function formatAttachmentLinks(Collection $links): array
    {
        return $links
            ->map(function (array $link): array {
                return [
                    'name' => (string) ($link['name'] ?? 'Attachment'),
                    'url' => (string) ($link['url'] ?? '#'),
                ];
            })
            ->values()
            ->all();
    }

    private function averagePerformanceRating(string $fromDate, string $toDate, string $selectedStatus): ?float
    {
        $value = DriverPerformanceEvaluation::query()
            ->where('status', 'Submitted')
            ->whereNotNull('overall_rating')
            ->whereHas('transportationRequestForm', function (Builder $query) use ($fromDate, $toDate, $selectedStatus) {
                $query->whereDate('request_date', '>=', $fromDate)
                    ->whereDate('request_date', '<=', $toDate);

                $this->applyStatusFilter($query, $selectedStatus);
            })
            ->avg('overall_rating');

        if ($value === null) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function syncMissingPerformanceEvaluations(string $fromDate, string $toDate, string $selectedStatus): void
    {
        $eligibleRequests = TransportationRequestFormModel::query()
            ->whereDate('request_date', '>=', $fromDate)
            ->whereDate('request_date', '<=', $toDate)
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->when(strtolower($selectedStatus) !== 'all', function (Builder $query) use ($selectedStatus) {
                $query->where('status', $selectedStatus);
            })
            ->get(['id', 'driver_name']);

        foreach ($eligibleRequests as $eligibleRequest) {
            $evaluationCopies = $this->buildEvaluationCopies(
                (int) $eligibleRequest->id,
                (string) ($eligibleRequest->driver_name ?? '')
            );

            foreach ($evaluationCopies as $copy) {
                DriverPerformanceEvaluation::query()->firstOrCreate(
                    [
                        'transportation_request_form_id' => (int) $eligibleRequest->id,
                        'copy_key' => (string) ($copy['copy_key'] ?? ''),
                    ],
                    [
                        'copy_number' => (int) ($copy['copy_number'] ?? 1),
                        'driver_name' => (string) ($copy['driver_name'] ?? 'N/A'),
                        'status' => 'Pending',
                    ]
                );
            }
        }
    }

    private function buildEvaluationCopies(int $requestId, string $driverNamesValue): array
    {
        $driverNames = $this->extractNameTokens($driverNamesValue);
        if (empty($driverNames)) {
            $driverNames = ['N/A'];
        }

        return collect($driverNames)
            ->values()
            ->map(function (string $driverName, int $index) use ($requestId): array {
                $copyNumber = $index + 1;

                return [
                    'copy_key' => $this->buildEvaluationCopyKey($requestId, $driverName, $copyNumber),
                    'copy_number' => $copyNumber,
                    'driver_name' => $driverName,
                ];
            })
            ->all();
    }

    private function buildEvaluationCopyKey(int $requestId, string $driverName, int $copyNumber): string
    {
        $seed = $requestId . '|' . strtolower(trim($driverName)) . '|' . $copyNumber;

        return substr(hash('sha256', $seed), 0, 32);
    }

    private function sortByRequestDate(Collection $rows, string $selectedSort, callable $dateResolver): Collection
    {
        if ($selectedSort === 'date_asc') {
            return $rows->sortBy(function ($row) use ($dateResolver): int {
                return $this->resolveTimestamp($dateResolver($row));
            })->values();
        }

        return $rows->sortByDesc(function ($row) use ($dateResolver): int {
            return $this->resolveTimestamp($dateResolver($row));
        })->values();
    }

    private function resolveTimestamp(mixed $value): int
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_string($value) && trim($value) !== '') {
            $timestamp = strtotime($value);

            return $timestamp !== false ? $timestamp : 0;
        }

        return 0;
    }

    private function buildTopDrivers(string $fromDate, string $toDate, string $selectedStatus): array
    {
        $evaluations = DriverPerformanceEvaluation::query()
            ->where('status', 'Submitted')
            ->whereNotNull('overall_rating')
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->whereHas('transportationRequestForm', function (Builder $query) use ($fromDate, $toDate, $selectedStatus) {
                $query->whereDate('request_date', '>=', $fromDate)
                    ->whereDate('request_date', '<=', $toDate);

                $this->applyStatusFilter($query, $selectedStatus);
            })
            ->get(['driver_name', 'overall_rating']);

        $stats = [];

        foreach ($evaluations as $evaluation) {
            $driverName = trim((string) ($evaluation->driver_name ?? ''));
            $rating = is_numeric($evaluation->overall_rating)
                ? (float) $evaluation->overall_rating
                : null;

            if ($driverName === '' || $rating === null) {
                continue;
            }

            $key = strtolower($driverName);

            if (!array_key_exists($key, $stats)) {
                $stats[$key] = [
                    'name' => $driverName,
                    'ratingsTotal' => 0.0,
                    'evaluations' => 0,
                ];
            }

            $stats[$key]['ratingsTotal'] += $rating;
            $stats[$key]['evaluations']++;
        }

        $rows = [];
        foreach ($stats as $stat) {
            $evaluationCount = max(1, (int) ($stat['evaluations'] ?? 0));
            $score = round(((float) ($stat['ratingsTotal'] ?? 0.0)) / $evaluationCount, 2);

            $rows[] = [
                'name' => (string) ($stat['name'] ?? 'N/A'),
                'initials' => $this->driverInitials((string) ($stat['name'] ?? '')),
                'score' => $score,
                'scoreLabel' => number_format($score, 2) . '/5.0',
                'trips' => $evaluationCount,
                'badge' => $score >= 4.5 ? 'ELITE' : ($score >= 3.5 ? 'PRO' : 'ACTIVE'),
            ];
        }

        usort($rows, function (array $a, array $b): int {
            if ((float) $a['score'] === (float) $b['score']) {
                return (int) $b['trips'] <=> (int) $a['trips'];
            }

            return (float) $b['score'] <=> (float) $a['score'];
        });

        return array_slice($rows, 0, 3);
    }

    private function buildChartData(Collection $requests): array
    {
        if ($requests->isEmpty()) {
            return [
                'bars' => $this->defaultChartBars(),
                'labels' => ['N/A', 'N/A', 'N/A', 'N/A'],
            ];
        }

        $requestIds = $requests->pluck('id')->filter()->values();

        $fuelByRequest = FuelIssuance::query()
            ->whereIn('transportation_request_form_id', $requestIds)
            ->get([
                'transportation_request_form_id',
                'gasoline_quantity',
                'diesel_quantity',
                'fuel_save_quantity',
                'v_power_quantity',
            ])
            ->groupBy('transportation_request_form_id')
            ->map(function (Collection $rows): float {
                return (float) $rows->sum(function ($row): float {
                    return (float) $row->gasoline_quantity
                        + (float) $row->diesel_quantity
                        + (float) $row->fuel_save_quantity
                        + (float) $row->v_power_quantity;
                });
            });

        $daily = [];
        foreach ($requests as $request) {
            $day = optional($request->request_date)->format('Y-m-d');
            if (!$day) {
                continue;
            }

            if (!array_key_exists($day, $daily)) {
                $daily[$day] = [
                    'fuel' => 0.0,
                    'distance' => 0.0,
                ];
            }

            $daily[$day]['fuel'] += (float) ($fuelByRequest->get($request->id) ?? 0.0);
            $daily[$day]['distance'] += $this->resolveDistance($request);
        }

        if (empty($daily)) {
            return [
                'bars' => $this->defaultChartBars(),
                'labels' => ['N/A', 'N/A', 'N/A', 'N/A'],
            ];
        }

        ksort($daily);
        $days = array_slice(array_keys($daily), -5);

        $maxFuel = 0.0;
        $maxDistance = 0.0;
        foreach ($days as $day) {
            $maxFuel = max($maxFuel, (float) ($daily[$day]['fuel'] ?? 0.0));
            $maxDistance = max($maxDistance, (float) ($daily[$day]['distance'] ?? 0.0));
        }

        $maxFuel = $maxFuel > 0 ? $maxFuel : 1.0;
        $maxDistance = $maxDistance > 0 ? $maxDistance : 1.0;

        $bars = [];
        foreach ($days as $day) {
            $fuel = (float) ($daily[$day]['fuel'] ?? 0.0);
            $distance = (float) ($daily[$day]['distance'] ?? 0.0);

            $bars[] = [
                'type' => 'fuel',
                'height' => $this->chartHeight($fuel, $maxFuel),
                'tooltip' => number_format($fuel, 1) . 'L',
            ];

            $bars[] = [
                'type' => 'distance',
                'height' => $this->chartHeight($distance, $maxDistance),
                'tooltip' => number_format($distance, 1) . ' km',
            ];
        }

        while (count($bars) < 10) {
            array_unshift($bars, [
                'type' => count($bars) % 2 === 0 ? 'fuel' : 'distance',
                'height' => 8,
                'tooltip' => '0',
            ]);
        }

        if (count($bars) > 10) {
            $bars = array_slice($bars, -10);
        }

        $labels = $this->buildChartLabels($days);

        return [
            'bars' => $bars,
            'labels' => $labels,
        ];
    }

    private function buildChartLabels(array $days): array
    {
        if (empty($days)) {
            return ['N/A', 'N/A', 'N/A', 'N/A'];
        }

        $lastIndex = count($days) - 1;
        $indices = [
            0,
            (int) floor($lastIndex / 3),
            (int) floor(($lastIndex * 2) / 3),
            $lastIndex,
        ];

        return collect($indices)
            ->map(function (int $index) use ($days): string {
                return Carbon::parse($days[$index])->format('M d');
            })
            ->all();
    }

    private function chartHeight(float $value, float $max): int
    {
        if ($value <= 0 || $max <= 0) {
            return 8;
        }

        return max(12, (int) round(($value / $max) * 100));
    }

    private function defaultChartBars(): array
    {
        return collect(range(1, 10))
            ->map(function (int $index): array {
                return [
                    'type' => $index % 2 === 1 ? 'fuel' : 'distance',
                    'height' => 8,
                    'tooltip' => '0',
                ];
            })
            ->all();
    }

    private function resolveDistance(TransportationRequestFormModel $request): float
    {
        $ticket = $request->dailyDriversTripTicket;

        $distance = is_numeric($ticket?->distance_travelled)
            ? (float) $ticket->distance_travelled
            : 0.0;

        if ($distance > 0) {
            return $distance;
        }

        if (is_numeric($ticket?->odometer_start) && is_numeric($ticket?->odometer_end)) {
            $computed = (float) $ticket->odometer_end - (float) $ticket->odometer_start;
            if ($computed > 0) {
                return round($computed, 2);
            }
        }

        if ($request->date_time_from && $request->date_time_to) {
            $from = Carbon::parse($request->date_time_from);
            $to = Carbon::parse($request->date_time_to);

            if ($to->greaterThan($from)) {
                return round($from->floatDiffInHours($to), 2);
            }
        }

        return 0.0;
    }

    private function extractNameTokens(string $names): array
    {
        $value = trim($names);
        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $tokens = $decoded;
        } else {
            $tokens = preg_split('/\s*\/\s*|\s*,\s*|\s*;\s*|\R+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
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

    private function driverInitials(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: []);
        if ($parts->isEmpty()) {
            return '--';
        }

        $first = strtoupper(substr((string) $parts->first(), 0, 1));
        $last = strtoupper(substr((string) $parts->last(), 0, 1));

        return ($first . $last) ?: '--';
    }
}

