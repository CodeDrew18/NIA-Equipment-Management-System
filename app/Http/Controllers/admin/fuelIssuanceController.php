<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\FuelIssuance;
use App\Models\FuelIssuancePartnership;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class fuelIssuanceController extends Controller
{
    private const DIVISION_MANAGER = 'ENGR. EMILIO M. DOMAGAS JR';
    private const FUEL_ATTACHMENT_KEY_PREFIX = 'fuel_issuance_file_';

    public function index(Request $request)
    {
        $payload = $this->buildPayload($request);

        return view('admin.fuel_issuance_slip.fuel_issuance', [
            'dispatchedRequests' => $payload['dispatchedRequests'],
            'selectedRequest' => $payload['selectedRequest'],
            'selectedCtrlNumber' => $payload['selectedCtrlNumber'],
            'selectedCopies' => $payload['selectedCopies'],
            'selectedFuelPartnership' => $payload['selectedFuelPartnership'],
            'search' => $payload['search'],
        ]);
    }

    public function data(Request $request)
    {
        $payload = $this->buildPayload($request);
        $requests = $payload['dispatchedRequests'];
        $selectedRequest = $payload['selectedRequest'];

        return response()->json([
            'filters' => [
                'search' => $payload['search'],
            ],
            'totalDispatchedRequests' => $requests->total(),
            'summaryText' => 'Showing ' . ($requests->firstItem() ?? 0) . '-' . ($requests->lastItem() ?? 0) . ' of ' . $requests->total() . ' dispatched requests',
            'pagination' => [
                'currentPage' => $requests->currentPage(),
                'lastPage' => $requests->lastPage(),
                'onFirstPage' => $requests->onFirstPage(),
                'hasMorePages' => $requests->hasMorePages(),
                'pageUrls' => $requests->getUrlRange(1, $requests->lastPage()),
            ],
            'requests' => $requests->getCollection()->map(function (TransportationRequestFormModel $item) {
                return [
                    'id' => $item->id,
                    'formId' => (string) $item->form_id,
                    'requestorName' => (string) $item->requestor_name,
                    'requestDate' => optional($item->request_date)->format('M d, Y') ?: 'N/A',
                    'vehicleId' => (string) ($item->vehicle_id ?: 'N/A'),
                    'driverName' => (string) ($item->driver_name ?: 'N/A'),
                    'canDispatchVehicle' => (bool) ($item->can_dispatch_vehicle ?? false),
                    'dispatchUrl' => route('admin.fuel_issuance_slip.dispatch', $item),
                ];
            })->values(),
            'selected' => [
                'id' => $selectedRequest?->id,
                'ctrlNumber' => $payload['selectedCtrlNumber'],
                'requestDate' => optional($selectedRequest?->request_date)->format('M d, Y') ?: '________________',
                'vehicleId' => (string) ($selectedRequest?->vehicle_id ?: '____________________________'),
                'driverName' => (string) ($selectedRequest?->driver_name ?: 'N/A'),
                'requestorName' => (string) ($selectedRequest?->requestor_name ?: '________________'),
                'divisionManagerName' => self::DIVISION_MANAGER,
                'fuelPartnership' => $payload['selectedFuelPartnership'],
                'canDispatchVehicle' => (bool) ($selectedRequest?->can_dispatch_vehicle ?? false),
                'copies' => $payload['selectedCopies'],
            ],
        ]);
    }

    public function dispatchVehicle(Request $request, TransportationRequestFormModel $transportationRequest)
    {
        if ($transportationRequest->status !== 'Dispatched') {
            $message = 'This request is no longer available for dispatch from Fuel Issuance.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('admin.fuel_issuance_slip')
                ->withErrors(['fuel_issuance' => $message]);
        }

        if (!$this->hasPrintedFuelIssuanceAttachments($transportationRequest)) {
            $message = 'Please print all Fuel Issuance copies first before proceeding to dispatch vehicle.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('admin.fuel_issuance_slip')
                ->withErrors(['fuel_issuance' => $message]);
        }

        $validated = $request->validate([
            'fuel_issuance_copies' => ['nullable', 'array'],
            'fuel_issuance_copies.*.copy_key' => ['required_with:fuel_issuance_copies', 'string', 'max:64'],
            'fuel_issuance_copies.*.dealer' => ['nullable', 'string', 'max:255'],
            'fuel_issuance_copies.*.gasoline' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.gasoline_price' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.diesel' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.diesel_price' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.fuel_save' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.fuel_save_price' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.v_power' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.v_power_price' => ['nullable', 'numeric', 'min:0'],
            'fuel_issuance_copies.*.total_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $expectedCopies = collect($this->buildFuelIssuanceCopies($transportationRequest))
            ->keyBy(function (array $copy) {
                return (string) ($copy['copyKey'] ?? '');
            });

        $submittedCopies = collect($validated['fuel_issuance_copies'] ?? [])
            ->keyBy(function (array $copy) {
                return (string) ($copy['copy_key'] ?? '');
            });

        if ($submittedCopies->isNotEmpty()) {
            $missingCopyKeys = $expectedCopies->keys()->diff($submittedCopies->keys());
            $unexpectedCopyKeys = $submittedCopies->keys()->diff($expectedCopies->keys());

            if ($missingCopyKeys->isNotEmpty() || $unexpectedCopyKeys->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'fuel_issuance' => 'Fuel issuance data is incomplete. Please complete all transportation copies before dispatch.',
                ]);
            }
        }

        $activePartnership = $this->resolveActiveFuelPartnership();

        DB::transaction(function () use ($transportationRequest, $expectedCopies, $submittedCopies, $activePartnership) {
            $requestSnapshot = $this->buildRequestFormDataSnapshot($transportationRequest);
            $existingByCopyKey = FuelIssuance::query()
                ->where('transportation_request_form_id', $transportationRequest->id)
                ->get()
                ->keyBy('copy_key');

            foreach ($expectedCopies as $copyKey => $expectedCopy) {
                $copyPayload = (array) $submittedCopies->get($copyKey, []);
                $existingRecord = $existingByCopyKey->get((string) $copyKey);

                $gasolineQuantity = $this->resolveNumericValue($copyPayload['gasoline'] ?? null, (float) ($existingRecord?->gasoline_quantity ?? 0));
                $gasolinePrice = $this->resolveNumericValue($copyPayload['gasoline_price'] ?? null, (float) ($existingRecord?->gasoline_price ?? (float) ($activePartnership?->gasoline_price_per_liter ?? 0)));
                $dieselQuantity = $this->resolveNumericValue($copyPayload['diesel'] ?? null, (float) ($existingRecord?->diesel_quantity ?? 0));
                $dieselPrice = $this->resolveNumericValue($copyPayload['diesel_price'] ?? null, (float) ($existingRecord?->diesel_price ?? (float) ($activePartnership?->diesel_price_per_liter ?? 0)));
                $fuelSaveQuantity = $this->resolveNumericValue($copyPayload['fuel_save'] ?? null, (float) ($existingRecord?->fuel_save_quantity ?? 0));
                $fuelSavePrice = $this->resolveNumericValue($copyPayload['fuel_save_price'] ?? null, (float) ($existingRecord?->fuel_save_price ?? (float) ($activePartnership?->fuel_save_price_per_liter ?? 0)));
                $vPowerQuantity = $this->resolveNumericValue($copyPayload['v_power'] ?? null, (float) ($existingRecord?->v_power_quantity ?? 0));
                $vPowerPrice = $this->resolveNumericValue($copyPayload['v_power_price'] ?? null, (float) ($existingRecord?->v_power_price ?? (float) ($activePartnership?->v_power_price_per_liter ?? 0)));

                $dealer = trim((string) ($copyPayload['dealer'] ?? ''));
                if ($dealer === '') {
                    $dealer = trim((string) ($existingRecord?->dealer ?? ''));
                }

                $calculatedTotal = round(
                    ($gasolineQuantity * $gasolinePrice)
                        + ($dieselQuantity * $dieselPrice)
                        + ($fuelSaveQuantity * $fuelSavePrice)
                        + ($vPowerQuantity * $vPowerPrice),
                    2
                );

                FuelIssuance::query()->updateOrCreate(
                    [
                        'transportation_request_form_id' => $transportationRequest->id,
                        'copy_key' => (string) $copyKey,
                    ],
                    [
                        'fuel_issuance_partnership_id' => $activePartnership?->id,
                        'copy_number' => (int) ($expectedCopy['copyNumber'] ?? 1),
                        'ctrl_number' => (string) ($expectedCopy['ctrlNumber'] ?? ''),
                        'vehicle_id' => (string) ($expectedCopy['vehicleId'] ?? ''),
                        'driver_name' => (string) ($expectedCopy['driverName'] ?? 'N/A'),
                        'dealer' => $dealer,
                        'gasoline_quantity' => $gasolineQuantity,
                        'gasoline_price' => $gasolinePrice,
                        'diesel_quantity' => $dieselQuantity,
                        'diesel_price' => $dieselPrice,
                        'fuel_save_quantity' => $fuelSaveQuantity,
                        'fuel_save_price' => $fuelSavePrice,
                        'v_power_quantity' => $vPowerQuantity,
                        'v_power_price' => $vPowerPrice,
                        'total_amount' => $calculatedTotal,
                        'request_form_data' => $requestSnapshot,
                        'dispatched_at' => now(),
                    ]
                );
            }

            $transportationRequest->update([
                'status' => 'On Trip',
            ]);
        });

        $message = 'Vehicle dispatched successfully. The request is now listed in On Trip Vehicles.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'requestId' => $transportationRequest->id,
                'drivers' => $this->extractNameTokens((string) $transportationRequest->driver_name),
            ]);
        }

        return redirect()
            ->route('admin.fuel_issuance_slip')
            ->with('admin_fuel_issuance_success', $message);
    }

    public function printOfficeCopy(Request $request)
    {
        $validated = $request->validate([
            'request_id' => ['required', 'integer', 'exists:transportation_requests_forms,id'],
            'copy_key' => ['required', 'string', 'max:128'],
            'vehicle_id' => ['required', 'string', 'max:255'],
            'driver_name' => ['required', 'string', 'max:255'],
            'dealer' => ['required', 'string', 'max:255'],
            'gasoline' => ['required', 'numeric', 'min:0'],
            'diesel' => ['required', 'numeric', 'min:0'],
            'fuel_save' => ['required', 'numeric', 'min:0'],
            'v_power' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $selectedRequest = TransportationRequestFormModel::query()
            ->whereIn('status', ['Dispatched', 'On Trip'])
            ->findOrFail($validated['request_id']);

        $selectedCopies = $this->buildFuelIssuanceCopies($selectedRequest);
        $selectedCopy = collect($selectedCopies)->firstWhere('copyKey', (string) $validated['copy_key']);

        if (!is_array($selectedCopy)) {
            throw ValidationException::withMessages([
                'copy_key' => 'The selected transportation copy is invalid.',
            ]);
        }

        if (
            trim((string) ($selectedCopy['vehicleId'] ?? '')) !== trim((string) $validated['vehicle_id'])
            || trim((string) ($selectedCopy['driverName'] ?? '')) !== trim((string) $validated['driver_name'])
        ) {
            throw ValidationException::withMessages([
                'copy_key' => 'The selected transportation copy does not match the request assignment.',
            ]);
        }

        $templatePath = storage_path('app/public/forms/form_2_rev_08.xlsx');
        if (!is_readable($templatePath)) {
            abort(500, 'Template file not found: form_2_rev_08.xlsx');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $ctrlNumber = (string) ($selectedCopy['ctrlNumber'] ?? ('FIS-' . optional($selectedRequest->request_date)->format('Y') . '-' . str_pad((string) $selectedRequest->id, 4, '0', STR_PAD_LEFT)));
        $requestDate = optional($selectedRequest->request_date)->format('M d, Y') ?: '';

        // Office Copy
        $sheet->mergeCells('B10:C10');
        $sheet->setCellValue('B10', $ctrlNumber ?? '');

        $sheet->mergeCells('F11:G11');
        $sheet->setCellValue('F11', $requestDate ?? '');

        $sheet->mergeCells('C13:E13');
        $sheet->setCellValue('C13', (string) ($validated['dealer'] ?? ''));

        $sheet->mergeCells('D15:F15');
        $sheet->setCellValue('D15', (string) ($selectedCopy['vehicleId'] ?? ''));

        $sheet->mergeCells('E19:F19');
        $sheet->setCellValue('E19', (string) ($validated['gasoline'] ?? ''));

        $sheet->mergeCells('E20:F20');
        $sheet->setCellValue('E20', (string) ($validated['diesel'] ?? ''));

        $sheet->mergeCells('E21:F21');
        $sheet->setCellValue('E21', (string) ($validated['fuel_save'] ?? ''));

        $sheet->mergeCells('E22:F22');
        $sheet->setCellValue('E22', (string) ($validated['v_power'] ?? ''));

        $sheet->mergeCells('E24:F24');
        $sheet->setCellValue('E24', (string) ($validated['total_amount'] ?? ''));

        $sheet->mergeCells('C27:E27');
        $sheet->setCellValue('C27', (string) ($selectedCopy['driverName'] ?? ''));

        $sheet->mergeCells('C32:E32');
        $sheet->setCellValue('C32', self::DIVISION_MANAGER);


        // Dealer's Copy
        $sheet->mergeCells('J10:K10');
        $sheet->setCellValue('J10', $ctrlNumber ?? '');

        $sheet->mergeCells('N11:O11');
        $sheet->setCellValue('N11', $requestDate ?? '');

        $sheet->mergeCells('K13:M13');
        $sheet->setCellValue('K13', (string) ($validated['dealer'] ?? ''));

        $sheet->mergeCells('L15:N15');
        $sheet->setCellValue('L15', (string) ($selectedCopy['vehicleId'] ?? ''));

        $sheet->mergeCells('M19:N19');
        $sheet->setCellValue('M19', (string) ($validated['gasoline'] ?? ''));

        $sheet->mergeCells('M20:N20');
        $sheet->setCellValue('M20', (string) ($validated['diesel'] ?? ''));

        $sheet->mergeCells('M21:N21');
        $sheet->setCellValue('M21', (string) ($validated['fuel_save'] ?? ''));

        $sheet->mergeCells('M22:N22');
        $sheet->setCellValue('M22', (string) ($validated['v_power'] ?? ''));

        $sheet->mergeCells('M24:N24');
        $sheet->setCellValue('M24', (string) ($validated['total_amount'] ?? ''));

        $sheet->mergeCells('K27:M27');
        $sheet->setCellValue('K27', (string) ($selectedCopy['driverName'] ?? ''));

        $sheet->mergeCells('K32:M32');
        $sheet->setCellValue('K32', self::DIVISION_MANAGER);

        $outputDirectory = storage_path('app/public/generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $baseFormId = $selectedRequest->form_id ?: 'REQUEST';
        $safeFormId = preg_replace('/[^A-Za-z0-9._-]/', '_', $baseFormId) ?: 'REQUEST';
        $copyNumber = (int) ($selectedCopy['copyNumber'] ?? 1);
        $safeFileName = 'Fuel_Issuance_Office_Copy_' . $safeFormId . '_copy_' . $copyNumber . '_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx';
        $outputPath = $outputDirectory . DIRECTORY_SEPARATOR . $safeFileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        $attachmentPayload = [
            'file_name' => $safeFileName,
            'file_path' => 'generated_forms/' . $safeFileName,
            'process' => 'fuel_issuance',
            'process_key' => self::FUEL_ATTACHMENT_KEY_PREFIX . ((string) ($selectedCopy['copyKey'] ?? 'default')),
            'source' => 'fuel_issuance_print',
            'copy_key' => (string) ($selectedCopy['copyKey'] ?? ''),
        ];

        $this->persistFuelIssuanceAttachment($selectedRequest, $selectedCopy, $validated, $attachmentPayload);
        $selectedRequest->upsertAttachment($attachmentPayload);

        return response()->download($outputPath, $safeFileName)->deleteFileAfterSend(true);
    }

    private function buildRequestFormDataSnapshot(TransportationRequestFormModel $transportationRequest): array
    {
        return [
            'id' => $transportationRequest->id,
            'form_id' => (string) ($transportationRequest->form_id ?? ''),
            'request_date' => optional($transportationRequest->request_date)->toDateString(),
            'requested_by' => (string) ($transportationRequest->requested_by ?? ''),
            'destination' => (string) ($transportationRequest->destination ?? ''),
            'date_time_from' => optional($transportationRequest->date_time_from)->toDateTimeString(),
            'date_time_to' => optional($transportationRequest->date_time_to)->toDateTimeString(),
            'purpose' => (string) ($transportationRequest->purpose ?? ''),
            'vehicle_type' => (string) ($transportationRequest->vehicle_type ?? ''),
            'vehicle_quantity' => (int) ($transportationRequest->vehicle_quantity ?? 0),
            'vehicle_id' => (string) ($transportationRequest->vehicle_id ?? ''),
            'driver_name' => (string) ($transportationRequest->driver_name ?? ''),
            'status' => (string) ($transportationRequest->status ?? ''),
        ];
    }

    private function persistFuelIssuanceAttachment(
        TransportationRequestFormModel $transportationRequest,
        array $selectedCopy,
        array $validated,
        array $attachmentPayload
    ): void {
        $copyKey = trim((string) ($selectedCopy['copyKey'] ?? ''));
        if ($copyKey === '') {
            return;
        }

        $activePartnership = $this->resolveActiveFuelPartnership();

        $fuelIssuance = FuelIssuance::query()->firstOrNew([
            'transportation_request_form_id' => $transportationRequest->id,
            'copy_key' => $copyKey,
        ]);

        if (!$fuelIssuance->exists) {
            $fuelIssuance->fill([
                'fuel_issuance_partnership_id' => $activePartnership?->id,
                'copy_number' => (int) ($selectedCopy['copyNumber'] ?? 1),
                'ctrl_number' => (string) ($selectedCopy['ctrlNumber'] ?? ''),
                'vehicle_id' => (string) ($selectedCopy['vehicleId'] ?? ''),
                'driver_name' => (string) ($selectedCopy['driverName'] ?? 'N/A'),
                'dealer' => trim((string) ($validated['dealer'] ?? '')),
                'gasoline_quantity' => round((float) ($validated['gasoline'] ?? 0), 2),
                'gasoline_price' => round((float) ($activePartnership?->gasoline_price_per_liter ?? 0), 2),
                'diesel_quantity' => round((float) ($validated['diesel'] ?? 0), 2),
                'diesel_price' => round((float) ($activePartnership?->diesel_price_per_liter ?? 0), 2),
                'fuel_save_quantity' => round((float) ($validated['fuel_save'] ?? 0), 2),
                'fuel_save_price' => round((float) ($activePartnership?->fuel_save_price_per_liter ?? 0), 2),
                'v_power_quantity' => round((float) ($validated['v_power'] ?? 0), 2),
                'v_power_price' => round((float) ($activePartnership?->v_power_price_per_liter ?? 0), 2),
                'total_amount' => round((float) ($validated['total_amount'] ?? 0), 2),
                'request_form_data' => $this->buildRequestFormDataSnapshot($transportationRequest),
            ]);
        }

        $previousPath = trim((string) data_get($fuelIssuance->attachment, 'file_path', ''));
        $nextPath = trim((string) ($attachmentPayload['file_path'] ?? ''));

        if (
            $previousPath !== ''
            && $nextPath !== ''
            && $previousPath !== $nextPath
            && Storage::disk('public')->exists($previousPath)
        ) {
            Storage::disk('public')->delete($previousPath);
        }

        $fuelIssuance->fill([
            'attachment' => $attachmentPayload,
        ]);
        $fuelIssuance->save();
    }

    private function buildPayload(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $selectedRequestId = $request->query('request_id');

        $dispatchedRequests = TransportationRequestFormModel::query()
            ->where('status', 'Dispatched')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('form_id', 'like', '%' . $search . '%')
                        ->orWhere('requested_by', 'like', '%' . $search . '%')
                        ->orWhere('destination', 'like', '%' . $search . '%')
                        ->orWhere('vehicle_id', 'like', '%' . $search . '%')
                        ->orWhere('driver_name', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $requestIds = $dispatchedRequests->getCollection()
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->values()
            ->all();

        $printedCopyKeysByRequestId = $this->resolvePrintedCopyKeysByRequestIds($requestIds);

        $dispatchedRequests->getCollection()->transform(function (TransportationRequestFormModel $item) use ($printedCopyKeysByRequestId) {
            $printedCopyKeys = $printedCopyKeysByRequestId[(int) $item->id] ?? [];

            $item->setAttribute(
                'can_dispatch_vehicle',
                $this->hasPrintedFuelIssuanceAttachments($item, $printedCopyKeys)
            );

            return $item;
        });

        $selectedRequest = null;
        if ($selectedRequestId) {
            $selectedRequest = TransportationRequestFormModel::query()
                ->whereIn('status', ['Dispatched', 'On Trip'])
                ->whereKey($selectedRequestId)
                ->first();
        }

        if (!$selectedRequest) {
            $selectedRequest = $dispatchedRequests->first();
        }

        $selectedPrintedCopyKeys = [];
        if ($selectedRequest) {
            $selectedPrintedCopyKeys = $printedCopyKeysByRequestId[(int) $selectedRequest->id]
                ?? $this->resolvePrintedCopyKeysByRequestIds([(int) $selectedRequest->id])[(int) $selectedRequest->id]
                ?? [];

            $selectedRequest->setAttribute(
                'can_dispatch_vehicle',
                $this->hasPrintedFuelIssuanceAttachments($selectedRequest, $selectedPrintedCopyKeys)
            );
        }

        $selectedCtrlNumber = $selectedRequest
            ? 'FIS-' . optional($selectedRequest->request_date)->format('Y') . '-' . str_pad((string) $selectedRequest->id, 4, '0', STR_PAD_LEFT)
            : 'FIS-0000-0000';

        $selectedFuelPartnership = $this->resolveSelectedFuelPartnership($selectedRequest);

        $selectedCopies = collect($this->buildFuelIssuanceCopies($selectedRequest))
            ->map(function (array $copy) use ($selectedPrintedCopyKeys) {
                $copyKey = (string) ($copy['copyKey'] ?? '');
                $copy['isPrinted'] = $copyKey !== '' && in_array($copyKey, $selectedPrintedCopyKeys, true);

                return $copy;
            })
            ->values()
            ->all();

        return [
            'dispatchedRequests' => $dispatchedRequests,
            'selectedRequest' => $selectedRequest,
            'selectedCtrlNumber' => $selectedCtrlNumber,
            'selectedCopies' => $selectedCopies,
            'selectedFuelPartnership' => $selectedFuelPartnership,
            'search' => $search,
        ];
    }

    private function resolveSelectedFuelPartnership(?TransportationRequestFormModel $transportationRequest): array
    {
        $partnershipRecord = null;

        if ($transportationRequest) {
            $partnershipRecord = FuelIssuance::query()
                ->where('transportation_request_form_id', $transportationRequest->id)
                ->whereNotNull('fuel_issuance_partnership_id')
                ->with('fuelIssuancePartnership')
                ->orderByDesc('id')
                ->first()?->fuelIssuancePartnership;
        }

        if (!$partnershipRecord) {
            $partnershipRecord = $this->resolveActiveFuelPartnership();
        }

        if (!$partnershipRecord) {
            $today = now()->toDateString();

            return [
                'id' => null,
                'name' => 'Petron Fuel',
                'validFrom' => $today,
                'validUntil' => now()->addYear()->toDateString(),
                'validityLabel' => '1 year validity',
                'gasolinePricePerLiter' => 0,
                'dieselPricePerLiter' => 0,
                'fuelSavePricePerLiter' => 0,
                'vPowerPricePerLiter' => 0,
            ];
        }

        $validFrom = optional($partnershipRecord->valid_from)->toDateString();
        $validUntil = optional($partnershipRecord->valid_until)->toDateString();
        $validityLabel = '1 year validity';

        if ($validFrom && $validUntil) {
            $days = Carbon::parse($validFrom)->diffInDays(Carbon::parse($validUntil)) + 1;
            if ($days > 0) {
                $validityLabel = $days >= 365
                    ? '1 year validity'
                    : $days . ' day validity';
            }
        }

        return [
            'id' => $partnershipRecord->id,
            'name' => (string) ($partnershipRecord->partnership_name ?? 'Petron Fuel'),
            'validFrom' => $validFrom ?: now()->toDateString(),
            'validUntil' => $validUntil ?: now()->addYear()->toDateString(),
            'validityLabel' => $validityLabel,
            'gasolinePricePerLiter' => round((float) ($partnershipRecord->gasoline_price_per_liter ?? 0), 2),
            'dieselPricePerLiter' => round((float) ($partnershipRecord->diesel_price_per_liter ?? 0), 2),
            'fuelSavePricePerLiter' => round((float) ($partnershipRecord->fuel_save_price_per_liter ?? 0), 2),
            'vPowerPricePerLiter' => round((float) ($partnershipRecord->v_power_price_per_liter ?? 0), 2),
        ];
    }

    private function resolveActiveFuelPartnership(): ?FuelIssuancePartnership
    {
        return FuelIssuancePartnership::query()
            ->where('is_active', true)
            ->orderByDesc('valid_until')
            ->orderByDesc('id')
            ->first();
    }

    private function resolvePrintedCopyKeysByRequestIds(array $requestIds): array
    {
        $normalizedRequestIds = collect($requestIds)
            ->map(function ($requestId) {
                return (int) $requestId;
            })
            ->filter(function (int $requestId) {
                return $requestId > 0;
            })
            ->unique()
            ->values();

        if ($normalizedRequestIds->isEmpty()) {
            return [];
        }

        return FuelIssuance::query()
            ->whereIn('transportation_request_form_id', $normalizedRequestIds->all())
            ->get(['transportation_request_form_id', 'copy_key', 'attachment'])
            ->groupBy('transportation_request_form_id')
            ->map(function ($records) {
                return collect($records)
                    ->filter(function (FuelIssuance $record) {
                        return $this->hasPrintedFuelIssuanceAttachment($record);
                    })
                    ->pluck('copy_key')
                    ->map(function ($copyKey) {
                        return trim((string) $copyKey);
                    })
                    ->filter(function (string $copyKey) {
                        return $copyKey !== '';
                    })
                    ->unique()
                    ->values()
                    ->all();
            })
            ->all();
    }

    private function hasPrintedFuelIssuanceAttachments(TransportationRequestFormModel $transportationRequest, ?array $printedCopyKeys = null): bool
    {
        $expectedCopyKeys = collect($this->buildFuelIssuanceCopies($transportationRequest))
            ->map(function (array $copy) {
                return trim((string) ($copy['copyKey'] ?? ''));
            })
            ->filter(function (string $copyKey) {
                return $copyKey !== '';
            })
            ->values();

        if ($expectedCopyKeys->isEmpty()) {
            return false;
        }

        if (!is_array($printedCopyKeys)) {
            $printedCopyKeys = $this->resolvePrintedCopyKeysByRequestIds([(int) $transportationRequest->id])[(int) $transportationRequest->id] ?? [];
        }

        $normalizedPrintedCopyKeys = collect($printedCopyKeys)
            ->map(function ($copyKey) {
                return trim((string) $copyKey);
            })
            ->filter(function (string $copyKey) {
                return $copyKey !== '';
            })
            ->unique()
            ->values();

        return $expectedCopyKeys->diff($normalizedPrintedCopyKeys)->isEmpty();
    }

    private function hasPrintedFuelIssuanceAttachment(FuelIssuance $fuelIssuance): bool
    {
        $relativePath = trim((string) data_get($fuelIssuance->attachment, 'file_path', ''));

        return $relativePath !== '';
    }

    private function resolveNumericValue(mixed $incomingValue, float $fallback): float
    {
        if ($incomingValue === null) {
            return round($fallback, 2);
        }

        if (is_string($incomingValue) && trim($incomingValue) === '') {
            return round($fallback, 2);
        }

        if (!is_numeric($incomingValue)) {
            return round($fallback, 2);
        }

        return round((float) $incomingValue, 2);
    }

    private function buildFuelIssuanceCopies(?TransportationRequestFormModel $transportationRequest): array
    {
        if (!$transportationRequest) {
            return [];
        }

        $baseCtrlNumber = 'FIS-' . optional($transportationRequest->request_date)->format('Y') . '-' . str_pad((string) $transportationRequest->id, 4, '0', STR_PAD_LEFT);
        $vehicleCodes = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);
        $driverNames = $this->extractNameTokens((string) $transportationRequest->driver_name);

        if (empty($vehicleCodes)) {
            $fallbackVehicle = trim((string) ($transportationRequest->vehicle_id ?: '____________________________'));
            $fallbackDriver = trim((string) ($driverNames[0] ?? $transportationRequest->driver_name ?? 'N/A'));

            return [[
                'copyKey' => substr(md5($transportationRequest->id . '|fallback|' . $fallbackVehicle . '|0'), 0, 32),
                'copyNumber' => 1,
                'ctrlNumber' => $baseCtrlNumber,
                'vehicleId' => $fallbackVehicle,
                'driverName' => $fallbackDriver !== '' ? $fallbackDriver : 'N/A',
            ]];
        }

        $vehiclesByCode = AdminVehicleAvailability::query()
            ->whereIn('vehicle_code', $vehicleCodes)
            ->get(['vehicle_code', 'driver_name'])
            ->keyBy('vehicle_code');

        $hasMultipleCopies = count($vehicleCodes) > 1;

        return collect($vehicleCodes)
            ->values()
            ->map(function (string $vehicleCode, int $index) use ($transportationRequest, $vehiclesByCode, $driverNames, $baseCtrlNumber, $hasMultipleCopies) {
                $resolvedDriver = trim((string) optional($vehiclesByCode->get($vehicleCode))->driver_name);
                if ($resolvedDriver === '') {
                    $resolvedDriver = trim((string) ($driverNames[$index] ?? ''));
                }
                if ($resolvedDriver === '') {
                    $resolvedDriver = trim((string) ($driverNames[0] ?? ''));
                }

                $copyNumber = $index + 1;
                $ctrlNumber = $hasMultipleCopies
                    ? $baseCtrlNumber . '-' . str_pad((string) $copyNumber, 2, '0', STR_PAD_LEFT)
                    : $baseCtrlNumber;

                return [
                    'copyKey' => substr(md5($transportationRequest->id . '|' . $vehicleCode . '|' . $index), 0, 32),
                    'copyNumber' => $copyNumber,
                    'ctrlNumber' => $ctrlNumber,
                    'vehicleId' => $vehicleCode,
                    'driverName' => $resolvedDriver !== '' ? $resolvedDriver : 'N/A',
                ];
            })
            ->all();
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
            ->values()
            ->all();
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
            $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
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
