<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\DailyDriversTripTicket;
use App\Models\TransportationRequestFormModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class dailyTripTicketController extends Controller
{
    private const DTT_STATUS_OPTIONS = ['Signed', 'Dispatched'];
    private const DTT_ATTACHMENT_KEY = 'daily_drivers_trip_ticket_file';

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
                $driverTargets = $this->buildDriverDownloadTargets($item);

                return [
                    'formId' => $item->form_id,
                    'vehicleType' => $item->vehicle_type,
                    'requestorName' => $item->requestor_name,
                    'requestorInitials' => strtoupper(substr($item->requestor_name, 0, 2)),
                    'dateRangeLabel' => $this->dateRangeLabel($item),
                    'daysTotalLabel' => $this->daysTotalLabel($item),
                    'dttCount' => $this->dttCount($item),
                    'status' => (string) ($item->status ?? 'Signed'),
                    'canDispatch' => (bool) ($item->can_dispatch ?? false),
                    'attachments' => is_array($item->attachment_links ?? null)
                        ? $item->attachment_links
                        : $this->buildAttachmentLinks($item),
                    'driverTargets' => $driverTargets,
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

        if ($validated['status'] === 'Dispatched' && !$this->hasPrintedDttAttachment($transportationRequest)) {
            $message = 'Please print the Daily Driver\'s Trip Ticket first before setting status to Dispatched.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('admin.daily-trip-ticket')
                ->withErrors(['admin_dtt' => $message]);
        }

        $assignedVehicleCodes = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);
        $vehicleStatus = $validated['status'] === 'Dispatched' ? 'On Business Trip' : 'Reserved';

        DB::transaction(function () use ($transportationRequest, $validated, $assignedVehicleCodes, $vehicleStatus) {
            $transportationRequest->update([
                'status' => $validated['status'],
            ]);

            if ($validated['status'] === 'Dispatched') {
                $vehicleDriverMap = is_array($transportationRequest->vehicle_driver_map)
                    ? $transportationRequest->vehicle_driver_map
                    : [];
                $assignedVehicleCodesList = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);
                $requestFormDataSnapshot = $this->buildRequestFormDataSnapshot($transportationRequest);

                if (!empty($assignedVehicleCodesList)) {
                    // Create one DTT per vehicle
                    foreach ($assignedVehicleCodesList as $vehicleCode) {
                        $driverForVehicle = trim((string) ($vehicleDriverMap[$vehicleCode] ?? ''));

                        // Fallback: use full driver_name if map has no entry
                        if ($driverForVehicle === '') {
                            $driverForVehicle = trim((string) ($transportationRequest->driver_name ?? ''));
                        }

                        DailyDriversTripTicket::query()->updateOrCreate(
                            [
                                'transportation_request_form_id' => $transportationRequest->id,
                                'assigned_vehicle_code' => $vehicleCode,
                            ],
                            [
                                'assigned_driver_name' => $driverForVehicle !== '' ? $driverForVehicle : null,
                                'request_form_data' => $requestFormDataSnapshot,
                            ]
                        );
                    }
                } else {
                    // No vehicle codes: create a single DTT
                    $assignedDriverNames = $this->parseDriverNames((string) ($transportationRequest->driver_name ?? ''));
                    $driverForDtt = !empty($assignedDriverNames) ? implode(' / ', $assignedDriverNames) : null;

                    DailyDriversTripTicket::query()->updateOrCreate(
                        [
                            'transportation_request_form_id' => $transportationRequest->id,
                            'assigned_vehicle_code' => null,
                        ],
                        [
                            'assigned_driver_name' => $driverForDtt,
                            'request_form_data' => $requestFormDataSnapshot,
                        ]
                    );
                }
            }

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

        $assignedVehicleCodes = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);
        $vehicleDriverMap = is_array($transportationRequest->vehicle_driver_map)
            ? $transportationRequest->vehicle_driver_map
            : [];

        $requestedVehicle = trim((string) request()->query('vehicle', ''));
        if ($requestedVehicle !== '') {
            if (!in_array($requestedVehicle, $assignedVehicleCodes, true)) {
                abort(404, 'Requested vehicle is not assigned to this transportation request.');
            }

            $driverForVehicle = trim((string) ($vehicleDriverMap[$requestedVehicle] ?? ''));
            if ($driverForVehicle === '') {
                $driverForVehicle = trim((string) ($transportationRequest->driver_name ?? ''));
            }

            $target = $this->ensureDttAttachmentDownloadTarget(
                $transportationRequest,
                $templatePath,
                $requestedVehicle,
                $driverForVehicle !== '' ? $driverForVehicle : null,
                $passengerNames
            );

            if ($target === null) {
                abort(404, 'Vehicle DTT attachment not found.');
            }

            return response()->download($target['absolutePath'], $target['downloadName']);
        }

        $requestedDriver = trim((string) request()->query('driver', ''));
        if ($requestedDriver !== '') {
            // Legacy: download by driver name query param
            $target = $this->ensureDttAttachmentDownloadTarget(
                $transportationRequest,
                $templatePath,
                null,
                $requestedDriver,
                $passengerNames
            );

            if ($target !== null) {
                return response()->download($target['absolutePath'], $target['downloadName']);
            }

            abort(404, 'Driver DTT attachment not found.');
        }

        $downloadTargets = [];

        if (!empty($assignedVehicleCodes)) {
            // Generate one DTT per vehicle
            foreach ($assignedVehicleCodes as $vehicleCode) {
                $driverForVehicle = trim((string) ($vehicleDriverMap[$vehicleCode] ?? ''));
                if ($driverForVehicle === '') {
                    $driverForVehicle = trim((string) ($transportationRequest->driver_name ?? ''));
                }

                $target = $this->ensureDttAttachmentDownloadTarget(
                    $transportationRequest,
                    $templatePath,
                    $vehicleCode,
                    $driverForVehicle !== '' ? $driverForVehicle : null,
                    $passengerNames
                );

                if ($target === null) {
                    continue;
                }

                $downloadTargets[] = $target;
            }
        } else {
            // No vehicle codes: generate a single DTT
            $target = $this->ensureDttAttachmentDownloadTarget(
                $transportationRequest,
                $templatePath,
                null,
                trim((string) ($transportationRequest->driver_name ?? '')) ?: null,
                $passengerNames
            );

            if ($target !== null) {
                $downloadTargets[] = $target;
            }
        }

        if (empty($downloadTargets)) {
            return response()->json(['message' => 'No DTT attachment found'], 404);
        }

        return response()->download(
            $downloadTargets[0]['absolutePath'],
            $downloadTargets[0]['downloadName']
        );
    }

    private function ensureDttAttachmentDownloadTarget(
        TransportationRequestFormModel $transportationRequest,
        string $templatePath,
        ?string $vehicleCode,
        ?string $driverName,
        string $passengerNames
    ): ?array {
        $expectedDriver = trim((string) ($driverName ?? ''));
        $snapshot = $this->buildRequestFormDataSnapshot($transportationRequest);

        $ticket = $this->resolveDttTicketQuery($transportationRequest->id, $vehicleCode)->first();
        if ($this->canReuseDttAttachment($ticket, $snapshot, $expectedDriver, $vehicleCode)) {
            $relativePath = trim((string) data_get($ticket?->attachment, 'file_path', ''));
            $fileName = trim((string) data_get($ticket?->attachment, 'file_name', ''));

            return [
                'absolutePath' => Storage::disk('public')->path($relativePath),
                'downloadName' => $fileName !== '' ? $fileName : basename($relativePath),
            ];
        }

        $this->generateDttForVehicle(
            $transportationRequest,
            $templatePath,
            $vehicleCode,
            $expectedDriver !== '' ? $expectedDriver : null,
            $passengerNames
        );

        $ticket = $this->resolveDttTicketQuery($transportationRequest->id, $vehicleCode)->first();
        $relativePath = trim((string) data_get($ticket?->attachment, 'file_path', ''));
        $fileName = trim((string) data_get($ticket?->attachment, 'file_name', ''));

        if ($relativePath === '' || !Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return [
            'absolutePath' => Storage::disk('public')->path($relativePath),
            'downloadName' => $fileName !== '' ? $fileName : basename($relativePath),
        ];
    }

    private function resolveDttTicketQuery(int $transportationRequestId, ?string $vehicleCode)
    {
        $query = DailyDriversTripTicket::query()
            ->where('transportation_request_form_id', $transportationRequestId);

        if ($vehicleCode === null || trim((string) $vehicleCode) === '') {
            return $query->whereNull('assigned_vehicle_code');
        }

        return $query->where('assigned_vehicle_code', trim((string) $vehicleCode));
    }

    private function canReuseDttAttachment(
        ?DailyDriversTripTicket $ticket,
        array $expectedSnapshot,
        string $expectedDriver,
        ?string $expectedVehicleCode
    ): bool {
        if (!$ticket) {
            return false;
        }

        $relativePath = trim((string) data_get($ticket->attachment, 'file_path', ''));
        if ($relativePath === '' || !Storage::disk('public')->exists($relativePath)) {
            return false;
        }

        $storedVehicleCode = trim((string) ($ticket->assigned_vehicle_code ?? ''));
        $normalizedExpectedVehicleCode = trim((string) ($expectedVehicleCode ?? ''));
        if ($normalizedExpectedVehicleCode !== '' && $storedVehicleCode !== $normalizedExpectedVehicleCode) {
            return false;
        }

        if ($normalizedExpectedVehicleCode === '' && $storedVehicleCode !== '') {
            return false;
        }

        $storedDriver = trim((string) ($ticket->assigned_driver_name ?? ''));
        if ($expectedDriver !== '' && $storedDriver !== $expectedDriver) {
            return false;
        }

        $storedSnapshot = is_array($ticket->request_form_data)
            ? $ticket->request_form_data
            : [];

        return $storedSnapshot === $expectedSnapshot;
    }

    /**
     * Generate a DTT xlsx file for a specific vehicle (and its driver).
     * vehicleCode=null means no specific vehicle (legacy single-vehicle flow).
     */
    private function generateDttForVehicle(
        TransportationRequestFormModel $transportationRequest,
        string $templatePath,
        ?string $vehicleCode,
        ?string $driverName,
        string $passengerNames
    ): void {
        // Use Xlsx reader directly to skip auto-detection overhead and disable charts/drawings
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(false);
        $reader->setIncludeCharts(false);
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $from = $transportationRequest->date_time_from;
        $to = $transportationRequest->date_time_to;

        // Resolve vehicle ID to display: use vehicleCode if provided, otherwise full vehicle_id
        $vehicleDisplay = $vehicleCode ?? ((string) ($transportationRequest->vehicle_id ?: 'N/A'));

        $sheet->mergeCells('S6:V6');
        $sheet->setCellValue('S6', (string) ($transportationRequest->form_id ?? 'N/A'));

        $sheet->mergeCells('E9:L9');
        $sheet->setCellValue('E9', $vehicleDisplay);

        $sheet->mergeCells('O9:V9');
        $sheet->setCellValue('O9', (string) ($driverName ?: 'N/A'));

        $sheet->mergeCells('E10:V10');
        $sheet->setCellValue('E10', (string) ($passengerNames !== '' ? $passengerNames : 'N/A'));

        $sheet->mergeCells('E11:V11');
        $sheet->setCellValue('E11', (string) ($transportationRequest->destination ?: 'N/A'));

        $sheet->mergeCells('E12:V12');
        $sheet->setCellValue('E12', (string) ($transportationRequest->purpose ?: 'N/A'));

        $fromDate = $from ? Carbon::parse($from) : null;
        $toDate   = $to ? Carbon::parse($to) : null;

        $sheet->mergeCells('H13:M13');
        $sheet->setCellValue('H13', $fromDate ? $fromDate->format('d/m/Y') : 'N/A');
        $sheet->mergeCells('P13:S13');
        $sheet->setCellValue('P13', $fromDate ? $fromDate->format('H:i') : 'N/A');

        $sheet->mergeCells('H15:M15');
        $sheet->setCellValue('H15', $toDate ? $toDate->format('d/m/Y') : 'N/A');
        $sheet->mergeCells('P15:S15');
        $sheet->setCellValue('P15', $toDate ? $toDate->format('H:i') : 'N/A');

        $sheet->mergeCells('P49:V49');
        $sheet->setCellValue('P49', (string) ($driverName ?: 'N/A'));

        $outputDirectory = Storage::disk('public')->path('generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $vehicleSuffix = $vehicleCode ? '_' . Str::slug(substr($vehicleCode, 0, 12)) : '';
        $fileName = 'DTT_' . ($transportationRequest->form_id ?: 'REQUEST') . $vehicleSuffix . '_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx';
        $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?: ('DTT_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx');
        $relativePath = 'generated_forms/' . $safeFileName;
        $outputPath = Storage::disk('public')->path($relativePath);

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $attachmentPayload = [
            'file_name' => $safeFileName,
            'file_path' => $relativePath,
            'process' => 'daily_drivers_trip_ticket',
            'process_key' => self::DTT_ATTACHMENT_KEY . '_' . ($vehicleCode ? Str::slug($vehicleCode) : 'unassigned'),
            'source' => 'daily_trip_ticket_download',
        ];

        $ticket = DailyDriversTripTicket::query()->updateOrCreate(
            [
                'transportation_request_form_id' => $transportationRequest->id,
                'assigned_vehicle_code' => $vehicleCode,
            ],
            ['request_form_data' => $this->buildRequestFormDataSnapshot($transportationRequest)]
        );

        $previousPath = trim((string) data_get($ticket->attachment, 'file_path', ''));
        if (
            $previousPath !== ''
            && $previousPath !== $relativePath
            && Storage::disk('public')->exists($previousPath)
        ) {
            Storage::disk('public')->delete($previousPath);
        }

        $ticket->update([
            'assigned_driver_name' => $driverName,
            'attachment' => $attachmentPayload,
        ]);

        $transportationRequest->upsertAttachment($attachmentPayload);
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
            ->where('status', 'Signed')
            ->with(['dailyDriversTripTicket:id,transportation_request_form_id,attachment']);
        $this->applyAssignmentReadyFilter($signedRequestsQuery);

        $requests = $signedRequestsQuery
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $requests->getCollection()->transform(function (TransportationRequestFormModel $item) {
            $item->setAttribute('can_dispatch', $this->hasPrintedDttAttachment($item));
            $item->setAttribute('normalized_attachments', $item->normalizeAttachments());
            $item->setAttribute('attachment_links', $this->buildAttachmentLinks($item));
            $item->setAttribute('driver_targets', $this->buildDriverDownloadTargets($item));

            return $item;
        });

        $totalDttsQuery = (clone $filteredQuery)->where('status', 'Signed');
        $this->applyAssignmentReadyFilter($totalDttsQuery);
        $totalDtts = DailyDriversTripTicket::query()
            ->whereHas('transportationRequestForm', function ($query) use ($totalDttsQuery) {
                $query->whereIn('id', (clone $totalDttsQuery)->pluck('id'));
            })
            ->count();

        $pendingDttsQuery = (clone $filteredQuery)->where('status', 'Signed');
        $this->applyAssignmentReadyFilter($pendingDttsQuery);
        $pendingDtts = DailyDriversTripTicket::query()
            ->whereHas('transportationRequestForm', function ($query) use ($pendingDttsQuery) {
                $query->whereIn('id', (clone $pendingDttsQuery)->pluck('id'));
            })
            ->whereNull('arrival_time_office') // Not completed
            ->count();

        // Count completed DTTs: tickets where arrival_time_office is filled (trip completed)
        $completedDtts = DailyDriversTripTicket::query()
            ->whereHas('transportationRequestForm', function ($query) use ($filteredQuery) {
                $query->whereIn('id', (clone $filteredQuery)->pluck('id'));
            })
            ->whereNotNull('arrival_time_office')
            ->count();

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

    private function buildRequestFormDataSnapshot(TransportationRequestFormModel $transportationRequest): array
    {
        return [
            'transportation_request_form_id' => $transportationRequest->id,
            'form_id' => (string) $transportationRequest->form_id,
            'form_creator_id' => (string) ($transportationRequest->form_creator_id ?? ''),
            'request_date' => optional($transportationRequest->request_date)->toDateString(),
            'requested_by' => (string) ($transportationRequest->requested_by ?? ''),
            'requestor_name' => (string) ($transportationRequest->requestor_name ?? ''),
            'destination' => (string) ($transportationRequest->destination ?? ''),
            'date_time_from' => optional($transportationRequest->date_time_from)->toDateTimeString(),
            'date_time_to' => optional($transportationRequest->date_time_to)->toDateTimeString(),
            'vehicle_type' => (string) ($transportationRequest->vehicle_type ?? ''),
            'vehicle_quantity' => $transportationRequest->vehicle_quantity,
            'vehicle_id' => (string) ($transportationRequest->vehicle_id ?? ''),
            'driver_name' => (string) ($transportationRequest->driver_name ?? ''),
            'status' => (string) ($transportationRequest->status ?? ''),
        ];
    }

    private function hasPrintedDttAttachment(TransportationRequestFormModel $transportationRequest): bool
    {
        $assignedVehicleCodes = $this->extractVehicleCodes((string) ($transportationRequest->vehicle_id ?? ''));

        if (!empty($assignedVehicleCodes)) {
            foreach ($assignedVehicleCodes as $vehicleCode) {
                $ticket = DailyDriversTripTicket::query()
                    ->where('transportation_request_form_id', $transportationRequest->id)
                    ->where('assigned_vehicle_code', $vehicleCode)
                    ->first();

                $relativePath = trim((string) data_get($ticket?->attachment, 'file_path', ''));
                if ($relativePath === '' || !Storage::disk('public')->exists($relativePath)) {
                    return false;
                }
            }

            return true;
        }

        $ticket = DailyDriversTripTicket::query()
            ->where('transportation_request_form_id', $transportationRequest->id)
            ->whereNull('assigned_vehicle_code')
            ->first();

        $relativePath = trim((string) data_get($ticket?->attachment, 'file_path', ''));

        return $relativePath !== '' && Storage::disk('public')->exists($relativePath);
    }

    private function buildAttachmentLinks(TransportationRequestFormModel $transportationRequest): array
    {
        $attachments = is_array($transportationRequest->normalized_attachments ?? null)
            ? $transportationRequest->normalized_attachments
            : $transportationRequest->normalizeAttachments();

        $baseLinks = collect($attachments)
            ->filter(function (array $attachment) {
                return trim((string) ($attachment['process'] ?? '')) !== 'daily_drivers_trip_ticket';
            })
            ->map(function (array $attachment, int $index) use ($transportationRequest) {
                $fileName = trim((string) ($attachment['file_name'] ?? 'Attachment'));
                $filePath = trim((string) ($attachment['file_path'] ?? ''));

                return [
                    'name' => $fileName !== '' ? $fileName : 'Attachment',
                    'url' => route('admin.transportation-request.attachment.view', [
                        'transportationRequest' => $transportationRequest->id,
                        'index' => $index,
                    ]),
                    'file_path' => $filePath,
                ];
            })
            ->values();

        $dttTickets = DailyDriversTripTicket::query()
            ->where('transportation_request_form_id', $transportationRequest->id)
            ->get(['attachment', 'assigned_driver_name']);

        foreach ($dttTickets as $ticket) {
            $dttFilePath = trim((string) data_get($ticket->attachment, 'file_path', ''));
            $dttFileName = trim((string) data_get($ticket->attachment, 'file_name', ''));
            $driverName = trim((string) ($ticket->assigned_driver_name ?? ''));

            if (
                $dttFilePath === ''
                || !Storage::disk('public')->exists($dttFilePath)
                || $baseLinks->contains(function (array $link) use ($dttFilePath) {
                    return trim((string) ($link['file_path'] ?? '')) === $dttFilePath;
                })
            ) {
                continue;
            }

            $displayName = $dttFileName !== '' ? $dttFileName : basename($dttFilePath);
            if ($driverName !== '' && !str_contains(strtolower($displayName), strtolower($driverName))) {
                $displayName = $displayName . ' - ' . $driverName;
            }

            $baseLinks->push([
                'name' => $displayName,
                'url' => asset('storage/' . ltrim($dttFilePath, '/')),
                'file_path' => $dttFilePath,
            ]);
        }

        return $baseLinks
            ->map(function (array $link) {
                return [
                    'name' => (string) ($link['name'] ?? 'Attachment'),
                    'url' => (string) ($link['url'] ?? '#'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildDriverDownloadTargets(TransportationRequestFormModel $transportationRequest): array
    {
        $vehicleCodes = $this->extractVehicleCodes((string) ($transportationRequest->vehicle_id ?? ''));
        $vehicleDriverMap = is_array($transportationRequest->vehicle_driver_map)
            ? $transportationRequest->vehicle_driver_map
            : [];

        if (!empty($vehicleCodes)) {
            return collect($vehicleCodes)
                ->map(function (string $vehicleCode) use ($transportationRequest, $vehicleDriverMap) {
                    $driverLabel = trim((string) ($vehicleDriverMap[$vehicleCode] ?? ''));
                    $label = $vehicleCode . ($driverLabel !== '' ? ' - ' . $driverLabel : '');

                    return [
                        'name' => $label,
                        'downloadUrl' => route('admin.daily-trip-ticket.download', [
                            'transportationRequest' => $transportationRequest,
                            'vehicle' => $vehicleCode,
                        ]),
                    ];
                })
                ->values()
                ->all();
        }

        return [[
            'name' => 'Unassigned Vehicle',
            'downloadUrl' => route('admin.daily-trip-ticket.download', $transportationRequest),
        ]];
    }

    private function parseDriverNames(mixed $value): array
    {
        if (is_array($value)) {
            $tokens = $value;
        } else {
            $stringValue = trim((string) $value);
            if ($stringValue === '') {
                return [];
            }

            $decoded = json_decode($stringValue, true);
            if (is_array($decoded)) {
                $tokens = $decoded;
            } else {
                $tokens = preg_split('/\s*\/\s*|\s*,\s*|\s*;\s*|\R+/', $stringValue, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            }
        }

        return collect($tokens)
            ->map(function ($token) {
                if (is_array($token)) {
                    return trim((string) ($token['driver_name'] ?? $token['name'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $name) {
                return $name !== '';
            })
            ->values()
            ->all();
    }
}

