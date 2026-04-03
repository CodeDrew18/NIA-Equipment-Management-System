<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\TransportationRequestFormModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class dailyTripTicketController extends Controller
{
    private const DTT_STATUS_OPTIONS = ['Signed', 'Dispatched'];

    public function index(Request $request)
    {
        return view('admin.daily_drivers_trip_ticket.trip_ticket', $this->buildPayload($request));
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
                    'status' => (string) ($item->status ?? 'Signed'),
                    'downloadUrl' => route('admin.daily-trip-ticket.download', $item),
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

        $assignedVehicleCodes = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);
        $vehicleStatus = $validated['status'] === 'Dispatched' ? 'On Business Trip' : 'Reserved';

        DB::transaction(function () use ($transportationRequest, $validated, $assignedVehicleCodes, $vehicleStatus) {
            $transportationRequest->update([
                'status' => $validated['status'],
            ]);

            if (empty($assignedVehicleCodes)) {
                return;
            }

            AdminVehicleAvailability::query()
                ->whereIn('vehicle_code', $assignedVehicleCodes)
                ->update([
                    'status' => $vehicleStatus,
                ]);
        });

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

    public function download(TransportationRequestFormModel $transportationRequest)
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

        $sheet->mergeCells('S6:V6');
        $sheet->setCellValue('S6', (string) ($transportationRequest->form_id ?? 'N/A'));

        $sheet->mergeCells('E9:L9');
        $sheet->setCellValue('E9', (string) ($transportationRequest->vehicle_id ?: $transportationRequest->vehicle_id ?: 'N/A'));

        $sheet->mergeCells('O9:V9');
        $sheet->setCellValue('O9', (string) ($transportationRequest->driver_name ?: 'N/A'));

        $sheet->mergeCells('E10:V10');
        $sheet->setCellValue('E10', (string) ($passengerNames !== '' ? $passengerNames : 'N/A'));

        $sheet->mergeCells('E11:V11');
        $sheet->setCellValue('E11', (string) ($transportationRequest->destination ?: 'N/A'));

        $sheet->mergeCells('E12:V12');
        $sheet->setCellValue('E12', (string) ($transportationRequest->purpose ?: 'N/A'));

        $fromDate = $from ? Carbon::parse($from) : null;
        $toDate   = $to ? Carbon::parse($to) : null;

        // FROM
        $sheet->mergeCells('H13:M13');
        $sheet->setCellValue('H13', $fromDate ? $fromDate->format('d/m/Y') : 'N/A'); // Date
        $sheet->mergeCells('P13:S13');
        $sheet->setCellValue('P13', $fromDate ? $fromDate->format('H:i') : 'N/A');   // Time

        // TO
        $sheet->mergeCells('H15:M15');
        $sheet->setCellValue('H15', $toDate ? $toDate->format('d/m/Y') : 'N/A'); // Date
        $sheet->mergeCells('P15:S15');
        $sheet->setCellValue('P15', $toDate ? $toDate->format('H:i') : 'N/A');   // Time


        $outputDirectory = storage_path('app/public/generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $fileName = 'DTT_' . ($transportationRequest->form_id ?: 'REQUEST') . '_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx';
        $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?: ('DTT_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx');
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

        $filteredQuery = TransportationRequestFormModel::query()
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

        $signedRequestsQuery = (clone $filteredQuery)
            ->where('status', 'Signed');
        $this->applyAssignmentReadyFilter($signedRequestsQuery);

        $requests = $signedRequestsQuery
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totalDttsQuery = (clone $filteredQuery)->where('status', 'Signed');
        $this->applyAssignmentReadyFilter($totalDttsQuery);
        $totalDtts = $totalDttsQuery->count();

        $pendingDttsQuery = (clone $filteredQuery)->where('status', 'Signed');
        $this->applyAssignmentReadyFilter($pendingDttsQuery);
        $pendingDtts = $pendingDttsQuery->count();

        $completedDtts = (clone $filteredQuery)->whereIn('status', ['Dispatched', 'On Trip'])->count();

        $vehicleRowsQuery = (clone $filteredQuery)->where('status', 'Signed');
        $this->applyAssignmentReadyFilter($vehicleRowsQuery);
        $vehicleRows = $vehicleRowsQuery->pluck('vehicle_type');
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

    private function applyAssignmentReadyFilter($query): void
    {
        $query->whereNotNull('vehicle_id')
            ->where('vehicle_id', '!=', '')
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '');
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
}
