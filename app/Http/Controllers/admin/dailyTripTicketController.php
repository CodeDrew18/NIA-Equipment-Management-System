<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TransportationRequestFormModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class dailyTripTicketController extends Controller
{
    private const DTT_STATUS_OPTIONS = ['Pending', 'To be Signed', 'Dispatched'];

    public function index(Request $request)
    {
        return view('admin.daily_drivers_trip_ticket.blade.trip_ticket', $this->buildPayload($request));
    }

    public function data(Request $request)
    {
        $payload = $this->buildPayload($request);
        $requests = $payload['requests'];

        return response()->json([
            'metrics' => [
                'totalDtts' => $payload['totalDtts'],
                'pendingDtts' => $payload['pendingDtts'],
                'completedDtts' => $payload['completedDtts'],
                'vehicleTypeCounts' => $payload['vehicleTypeCounts'],
            ],
            'filters' => [
                'search' => $payload['search'],
                'vehicleType' => $payload['vehicleType'],
                'fromDate' => $payload['fromDate'],
                'toDate' => $payload['toDate'],
            ],
            'summaryText' => 'Showing ' . $requests->firstItem() . '-' . $requests->lastItem() . ' of ' . $requests->total() . ' requests',
            'pagination' => [
                'currentPage' => $requests->currentPage(),
                'lastPage' => $requests->lastPage(),
                'onFirstPage' => $requests->onFirstPage(),
                'hasMorePages' => $requests->hasMorePages(),
                'pageUrls' => $requests->getUrlRange(1, $requests->lastPage()),
            ],
            'requests' => $requests->getCollection()->map(function (TransportationRequestFormModel $item) {
                return [
                    'formId' => $item->form_id,
                    'vehicleType' => $item->vehicle_type,
                    'requestorName' => $item->requestor_name,
                    'requestorInitials' => strtoupper(substr($item->requestor_name, 0, 2)),
                    'dateRangeLabel' => $this->dateRangeLabel($item),
                    'daysTotalLabel' => $this->daysTotalLabel($item),
                    'dttCount' => $this->dttCount($item),
                    'status' => (string) ($item->status ?? 'Pending'),
                    'printUrl' => route('admin.daily-trip-ticket.print', $item),
                    'updateStatusUrl' => route('admin.daily-trip-ticket.status', $item),
                ];
            })->values(),
        ]);
    }

    public function updateStatus(Request $request, TransportationRequestFormModel $transportationRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::DTT_STATUS_OPTIONS)],
        ]);

        $transportationRequest->update([
            'status' => $validated['status'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'DTT status updated successfully.',
                'status' => $validated['status'],
            ]);
        }

        return redirect()
            ->route('admin.daily-trip-ticket')
            ->with('admin_dtt_success', 'DTT status updated to ' . $validated['status'] . '.');
    }

    public function print(TransportationRequestFormModel $transportationRequest)
    {
        $templatePath = storage_path('app/public/forms/DRIVERS_TRIP_TICKET_FORM-1_rev_09.xlsx');
        if (!is_readable($templatePath)) {
            abort(500, 'DTT template file not found: DRIVERS_TRIP_TICKET_FORM-1_rev_09.xlsx');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $from = $transportationRequest->date_time_from;
        $to = $transportationRequest->date_time_to;
        $requestDate = $transportationRequest->request_date;

        $passengerNames = collect(is_array($transportationRequest->business_passengers) ? $transportationRequest->business_passengers : [])
            ->map(function ($row) {
                if (is_array($row) && isset($row['name'])) {
                    return trim((string) $row['name']);
                }

                return is_string($row) ? trim($row) : '';
            })
            ->filter()
            ->values()
            ->implode(', ');

        // These cells are mapped for DTT template population.
        $sheet->setCellValue('B3', (string) ($transportationRequest->form_id ?? 'N/A'));
        $sheet->setCellValue('H3', (string) (($requestDate?->format('Y-m-d')) ?? now()->format('Y-m-d')));
        $sheet->setCellValue('B5', (string) ($transportationRequest->requestor_name ?: $transportationRequest->requested_by ?: 'N/A'));
        $sheet->setCellValue('H5', (string) ($transportationRequest->requestor_position ?: 'N/A'));
        $sheet->setCellValue('B7', (string) ($transportationRequest->destination ?: 'N/A'));
        $sheet->setCellValue('B9', (string) ($transportationRequest->purpose ?: 'N/A'));
        $sheet->setCellValue('B11', (string) ($transportationRequest->vehicle_type ?: 'N/A'));
        $sheet->setCellValue('H11', (string) ($transportationRequest->vehicle_quantity ?? '1'));
        $sheet->setCellValue('B13', (string) ($transportationRequest->driver_name ?: 'N/A'));
        $sheet->setCellValue('H13', (string) ($passengerNames !== '' ? $passengerNames : 'N/A'));
        $sheet->setCellValue('B15', (string) (($from?->format('Y-m-d H:i')) ?? 'N/A'));
        $sheet->setCellValue('H15', (string) (($to?->format('Y-m-d H:i')) ?? 'N/A'));
        $sheet->setCellValue('B17', $this->dateRangeLabel($transportationRequest));
        $sheet->setCellValue('H17', (string) $this->dttCount($transportationRequest));
        $sheet->setCellValue('B19', (string) ($transportationRequest->status ?? 'Pending'));

        $outputDirectory = storage_path('app/public/generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $fileName = 'DTT_' . ($transportationRequest->form_id ?: 'REQUEST') . '_' . now()->format('Ymd_His') . '.xlsx';
        $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?: ('DTT_' . now()->format('Ymd_His') . '.xlsx');
        $outputPath = $outputDirectory . DIRECTORY_SEPARATOR . $safeFileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        return response()->download($outputPath, $safeFileName)->deleteFileAfterSend(true);
    }

    private function buildPayload(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'vehicle_type' => ['nullable', 'in:coaster,van,pickup'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $vehicleType = $validated['vehicle_type'] ?? '';
        $fromDate = $validated['from'] ?? '';
        $toDate = $validated['to'] ?? '';

        $baseQuery = TransportationRequestFormModel::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('form_id', 'like', '%' . $search . '%')
                        ->orWhere('requested_by', 'like', '%' . $search . '%')
                        ->orWhere('vehicle_type', 'like', '%' . $search . '%');
                });
            })
            ->when($vehicleType !== '', function ($query) use ($vehicleType) {
                $query->where('vehicle_type', 'like', '%' . $vehicleType . '%');
            })
            ->when($fromDate !== '', function ($query) use ($fromDate) {
                $query->whereDate('request_date', '>=', $fromDate);
            })
            ->when($toDate !== '', function ($query) use ($toDate) {
                $query->whereDate('request_date', '<=', $toDate);
            });

        $pendingStatuses = ['Pending', 'To be Signed'];

        $requests = (clone $baseQuery)
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totalDtts = (clone $baseQuery)->count();
        $pendingDtts = (clone $baseQuery)->whereIn('status', $pendingStatuses)->count();
        $completedDtts = (clone $baseQuery)->whereNotIn('status', $pendingStatuses)->count();

        $vehicleRows = (clone $baseQuery)->pluck('vehicle_type');
        $vehicleTypeCounts = [
            'coaster' => 0,
            'van' => 0,
            'pickup' => 0,
        ];
        foreach ($vehicleRows as $vehicleLabel) {
            $label = strtolower((string) $vehicleLabel);
            foreach (array_keys($vehicleTypeCounts) as $key) {
                if (str_contains($label, $key)) {
                    $vehicleTypeCounts[$key]++;
                }
            }
        }

        return [
            'requests' => $requests,
            'totalDtts' => $totalDtts,
            'pendingDtts' => $pendingDtts,
            'completedDtts' => $completedDtts,
            'vehicleTypeCounts' => $vehicleTypeCounts,
            'search' => $search,
            'vehicleType' => $vehicleType,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ];
    }

    private function dateRangeLabel(TransportationRequestFormModel $request): string
    {
        $from = $request->date_time_from;
        $to = $request->date_time_to;

        if (!$from || !$to) {
            return 'N/A';
        }

        return $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y');
    }

    private function dttCount(TransportationRequestFormModel $request): int
    {
        $from = $request->date_time_from;
        $to = $request->date_time_to;

        if (!$from || !$to) {
            return 1;
        }

        return max(1, Carbon::parse($from)->startOfDay()->diffInDays(Carbon::parse($to)->startOfDay()) + 1);
    }

    private function daysTotalLabel(TransportationRequestFormModel $request): string
    {
        $days = $this->dttCount($request);
        return $days . ' Day' . ($days > 1 ? 's' : '') . ' Total';
    }
}
