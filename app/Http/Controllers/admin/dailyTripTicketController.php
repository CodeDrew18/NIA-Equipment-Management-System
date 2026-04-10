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
use PhpOffice\PhpSpreadsheet\IOFactory;
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
                // Get all assigned drivers
                $assignedDriverNames = $this->parseDriverNames((string) ($transportationRequest->driver_name ?? ''));
                $requestFormDataSnapshot = $this->buildRequestFormDataSnapshot($transportationRequest);

                // Create one DTT per driver
                foreach ($assignedDriverNames as $driverName) {
                    DailyDriversTripTicket::query()->updateOrCreate(
                        [
                            'transportation_request_form_id' => $transportationRequest->id,
                            'assigned_driver_name' => trim($driverName),
                        ],
                        ['request_form_data' => $requestFormDataSnapshot]
                    );
                }

                // If no drivers are assigned, create one DTT without driver assignment
                if (empty($assignedDriverNames)) {
                    DailyDriversTripTicket::query()->updateOrCreate(
                        [
                            'transportation_request_form_id' => $transportationRequest->id,
                            'assigned_driver_name' => null,
                        ],
                        ['request_form_data' => $requestFormDataSnapshot]
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

        $assignedDriverNames = $this->parseDriverNames((string) ($transportationRequest->driver_name ?? ''));

        $requestedDriver = trim((string) request()->query('driver', ''));
        if ($requestedDriver !== '') {
            $targetDriver = collect($assignedDriverNames)
                ->first(function (string $driverName) use ($requestedDriver) {
                    return mb_strtolower(trim($driverName)) === mb_strtolower($requestedDriver);
                });

            if (!$targetDriver) {
                abort(404, 'Requested driver is not assigned to this transportation request.');
            }

            $this->generateDttForDriver(
                $transportationRequest,
                $templatePath,
                $targetDriver,
                $passengerNames
            );

            $driverTicket = DailyDriversTripTicket::query()
                ->where('transportation_request_form_id', $transportationRequest->id)
                ->where('assigned_driver_name', $targetDriver)
                ->first();

            $driverFilePath = trim((string) data_get($driverTicket?->attachment, 'file_path', ''));
            $driverFileName = trim((string) data_get($driverTicket?->attachment, 'file_name', ''));

            if ($driverFilePath !== '' && Storage::disk('public')->exists($driverFilePath)) {
                return response()->download(
                    Storage::disk('public')->path($driverFilePath),
                    $driverFileName !== '' ? $driverFileName : basename($driverFilePath)
                );
            }

            abort(404, 'Driver DTT attachment not found.');
        }

        // Generate DTT for each driver
        foreach ($assignedDriverNames as $driverName) {
            $this->generateDttForDriver(
                $transportationRequest,
                $templatePath,
                $driverName,
                $passengerNames
            );
        }

        // If no drivers assigned, generate one DTT
        if (empty($assignedDriverNames)) {
            $this->generateDttForDriver(
                $transportationRequest,
                $templatePath,
                null,
                $passengerNames
            );
        }

        // Return the first DTT for download
        $firstDtt = DailyDriversTripTicket::query()
            ->where('transportation_request_form_id', $transportationRequest->id)
            ->first();

        if ($firstDtt && $firstDtt->attachment) {
            $filePath = trim((string) data_get($firstDtt->attachment, 'file_path', ''));
            if ($filePath !== '' && Storage::disk('public')->exists($filePath)) {
                $fileName = basename($filePath);
                return response()->download(Storage::disk('public')->path($filePath), $fileName);
            }
        }

        return response()->json(['message' => 'No DTT attachment found'], 404);
    }

    private function generateDttForDriver(
        TransportationRequestFormModel $transportationRequest,
        string $templatePath,
        ?string $driverName,
        string $passengerNames
    ): void {
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $from = $transportationRequest->date_time_from;
        $to = $transportationRequest->date_time_to;

        $sheet->mergeCells('S6:V6');
        $sheet->setCellValue('S6', (string) ($transportationRequest->form_id ?? 'N/A'));

        $sheet->mergeCells('E9:L9');
        $sheet->setCellValue('E9', (string) ($transportationRequest->vehicle_id ?: $transportationRequest->vehicle_id ?: 'N/A'));

        $sheet->mergeCells('O9:V9');
        // Show only the assigned driver for this DTT, not all drivers
        $sheet->setCellValue('O9', (string) ($driverName ?: (string) ($transportationRequest->driver_name ?: 'N/A')));

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
        $sheet->setCellValue('H13', $fromDate ? $fromDate->format('d/m/Y') : 'N/A');
        $sheet->mergeCells('P13:S13');
        $sheet->setCellValue('P13', $fromDate ? $fromDate->format('H:i') : 'N/A');

        // TO
        $sheet->mergeCells('H15:M15');
        $sheet->setCellValue('H15', $toDate ? $toDate->format('d/m/Y') : 'N/A');
        $sheet->mergeCells('P15:S15');
        $sheet->setCellValue('P15', $toDate ? $toDate->format('H:i') : 'N/A');

        $outputDirectory = Storage::disk('public')->path('generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $driverSuffix = $driverName ? '_' . Str::slug(substr($driverName, 0, 10)) : '';
        $fileName = 'DTT_' . ($transportationRequest->form_id ?: 'REQUEST') . $driverSuffix . '_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx';
        $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?: ('DTT_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx');
        $relativePath = 'generated_forms/' . $safeFileName;
        $outputPath = Storage::disk('public')->path($relativePath);

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        $attachmentPayload = [
            'file_name' => $safeFileName,
            'file_path' => $relativePath,
            'process' => 'daily_drivers_trip_ticket',
            'process_key' => self::DTT_ATTACHMENT_KEY . '_' . ($driverName ? Str::slug($driverName) : 'unassigned'),
            'source' => 'daily_trip_ticket_download',
        ];

        $ticket = DailyDriversTripTicket::query()->updateOrCreate(
            [
                'transportation_request_form_id' => $transportationRequest->id,
                'assigned_driver_name' => $driverName,
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
        // Check if any DTT for this request has a printed attachment
        $ticket = DailyDriversTripTicket::query()
            ->where('transportation_request_form_id', $transportationRequest->id)
            ->whereNotNull('attachment')
            ->first();

        if (!$ticket) {
            return false;
        }

        $relativePath = trim((string) data_get($ticket->attachment, 'file_path', ''));

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
        $driverNames = $this->parseDriverNames((string) ($transportationRequest->driver_name ?? ''));

        if (empty($driverNames)) {
            return [[
                'name' => 'Unassigned Driver',
                'downloadUrl' => route('admin.daily-trip-ticket.download', $transportationRequest),
            ]];
        }

        return collect($driverNames)
            ->map(function (string $driverName) use ($transportationRequest) {
                return [
                    'name' => $driverName,
                    'downloadUrl' => route('admin.daily-trip-ticket.download', $transportationRequest, [
                        'driver' => $driverName,
                    ]),
                ];
            })
            ->values()
            ->all();
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
                $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $stringValue, -1, PREG_SPLIT_NO_EMPTY) ?: [];
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
