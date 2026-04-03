<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class fuelIssuanceController extends Controller
{
    private const DIVISION_MANAGER = 'ENGR. EMILIO M. DOMAGAS JR';

    public function index(Request $request)
    {
        $payload = $this->buildPayload($request);

        return view('admin.fuel_issuance_slip.fuel_issuance', [
            'dispatchedRequests' => $payload['dispatchedRequests'],
            'selectedRequest' => $payload['selectedRequest'],
            'search' => $payload['search'],
        ]);
    }

    public function data(Request $request)
    {
        $payload = $this->buildPayload($request);
        $requests = $payload['dispatchedRequests'];
        $selectedRequest = $payload['selectedRequest'];

        $ctrlNumber = $selectedRequest
            ? 'FIS-' . optional($selectedRequest->request_date)->format('Y') . '-' . str_pad((string) $selectedRequest->id, 4, '0', STR_PAD_LEFT)
            : 'FIS-0000-0000';

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
                    'dispatchUrl' => route('admin.fuel_issuance_slip.dispatch', $item),
                ];
            })->values(),
            'selected' => [
                'id' => $selectedRequest?->id,
                'ctrlNumber' => $ctrlNumber,
                'requestDate' => optional($selectedRequest?->request_date)->format('M d, Y') ?: '________________',
                'vehicleId' => (string) ($selectedRequest?->vehicle_id ?: '____________________________'),
                'driverName' => (string) ($selectedRequest?->driver_name ?: 'N/A'),
                'requestorName' => (string) ($selectedRequest?->requestor_name ?: '________________'),
                'divisionManagerName' => self::DIVISION_MANAGER,
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

        $transportationRequest->update([
            'status' => 'On Trip',
        ]);

        $message = 'Vehicle dispatched successfully. The request is now listed in On Trip Vehicles.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'requestId' => $transportationRequest->id,
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

        $templatePath = storage_path('app/public/forms/form_2_rev_08.xlsx');
        if (!is_readable($templatePath)) {
            abort(500, 'Template file not found: form_2_rev_08.xlsx');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $ctrlNumber = 'FIS-' . optional($selectedRequest->request_date)->format('Y') . '-' . str_pad((string) $selectedRequest->id, 4, '0', STR_PAD_LEFT);
        $requestDate = optional($selectedRequest->request_date)->format('M d, Y') ?: '';

        // Office Copy
        $sheet->mergeCells('B10:C10');
        $sheet->setCellValue('B10', $ctrlNumber ?? '');

        $sheet->mergeCells('F11:G11');
        $sheet->setCellValue('F11', $requestDate ?? '');

        $sheet->mergeCells('C13:E13');
        $sheet->setCellValue('C13', (string) ($validated['dealer'] ?? ''));

        $sheet->mergeCells('D15:F15');
        $sheet->setCellValue('D15', (string) ($selectedRequest->vehicle_id ?? ''));

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
        $sheet->setCellValue('C27', (string) ($selectedRequest->driver_name ?? ''));

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
        $sheet->setCellValue('L15', (string) ($selectedRequest->vehicle_id ?? ''));

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
        $sheet->setCellValue('K27', (string) ($selectedRequest->driver_name ?? ''));

        $sheet->mergeCells('K32:M32');
        $sheet->setCellValue('K32', self::DIVISION_MANAGER);

        $outputDirectory = storage_path('app/public/generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $baseFormId = $selectedRequest->form_id ?: 'REQUEST';
        $safeFormId = preg_replace('/[^A-Za-z0-9._-]/', '_', $baseFormId) ?: 'REQUEST';
        $safeFileName = 'Fuel_Issuance_Office_Copy_' . $safeFormId . '_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx';
        $outputPath = $outputDirectory . DIRECTORY_SEPARATOR . $safeFileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        return response()->download($outputPath, $safeFileName)->deleteFileAfterSend(true);
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

        return [
            'dispatchedRequests' => $dispatchedRequests,
            'selectedRequest' => $selectedRequest,
            'search' => $search,
        ];
    }
}
