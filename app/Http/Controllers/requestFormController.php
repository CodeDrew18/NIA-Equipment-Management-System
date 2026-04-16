<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AdminVehicleAvailability;
use App\Models\TransportationRequestFormModel;
use App\Support\AssignatoryPersonnelResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class requestFormController extends Controller
{
    private const SYSADMIN_PERSONNEL_ID = '100001';
    private const REQUEST_FORM_ATTACHMENT_KEY = 'transportation_request_form_file';

    public function requestForm()
    {
        $user = Auth::user();

        $availableVehicleInventory = $this->resolveAvailableVehicleInventory();
        $availableVehicleTypes = $availableVehicleInventory['types'];
        $availableVehicleCounts = $availableVehicleInventory['counts'];
        $availableVehicleCapacities = $availableVehicleInventory['capacities'] ?? ['coaster' => null, 'van' => null, 'pickup' => null];

        $drivers = User::query()
            ->whereRaw("CONCAT(',', role, ',') LIKE '%,driver,%'")
            ->where('personnel_id', '!=', self::SYSADMIN_PERSONNEL_ID)
            ->orderBy('name')
            ->get(['name']);

        return view('letter_of_request/requestform', [
            'availableVehicleTypes' => $availableVehicleTypes,
            'availableVehicleCounts' => $availableVehicleCounts,
            'availableVehicleCapacities' => $availableVehicleCapacities,
            'drivers' => $drivers,
        ]);
    }

    public function submitRequestForm(Request $request)
    {
        $validated = $request->validate([
            'request_date' => ['required', 'date'],
            'requested_by' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'date_time_from' => ['required', 'date'],
            'date_time_to' => ['required', 'date', 'after:date_time_from'],
            'purpose' => ['required', 'string', 'max:2000'],
            'vehicle_requests' => ['required', 'array', 'min:1'],
            'vehicle_requests.*.type' => ['required', 'in:coaster,van,pickup'],
            'vehicle_requests.*.selected' => ['nullable', 'boolean'],
            'vehicle_requests.*.quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'division_personnel' => ['required', 'array', 'min:1'],
            'division_personnel.*.id_number' => [
                'required',
                'digits:6',
                Rule::exists('users', 'personnel_id')->where(function ($query) {
                    $query->whereRaw("CONCAT(',', role, ',') NOT LIKE '%,admin,%'");
                    $query->where('personnel_id', '!=', self::SYSADMIN_PERSONNEL_ID);
                }),
            ],
            'division_personnel.*.name' => ['required', 'string', 'max:255'],
            'requesting_division_name' => ['required', 'string', 'max:255'],
            'requesting_division_position' => ['required', 'string', 'max:255'],
            'vehicle_id' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['required', 'file', 'mimes:docx,pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'attachments.required' => 'Attach at least one file.',
            'attachments.min' => 'Attach at least one file.',
            'attachments.*.mimes' => 'Only DOCX, PDF, and image files (JPG, JPEG, PNG) are allowed.',
        ]);

        $businessPassengers = collect($validated['division_personnel'] ?? [])
            ->map(function (array $passenger) {
                return [
                    'id_number' => (string) ($passenger['id_number'] ?? ''),
                    'name' => trim((string) ($passenger['name'] ?? '')),
                ];
            })
            ->filter(function (array $passenger) {
                return $passenger['id_number'] !== '' && $passenger['name'] !== '';
            })
            ->values()
            ->all();

        $passengerNames = collect($businessPassengers)
            ->pluck('name')
            ->filter()
            ->unique()
            ->values();

        $purposeWithPassengers = trim($validated['purpose']);
        if ($passengerNames->isNotEmpty()) {
            $purposeWithPassengers .= "\nPassengers: " . $passengerNames->implode(', ');
        }

        $selectedVehicleRequests = collect($validated['vehicle_requests'])
            ->filter(function (array $vehicleRequest) {
                return !empty($vehicleRequest['selected']);
            })
            ->map(function (array $vehicleRequest) {
                return [
                    'type' => $vehicleRequest['type'],
                    'quantity' => (int) ($vehicleRequest['quantity'] ?? 0),
                ];
            })
            ->filter(function (array $vehicleRequest) {
                return $vehicleRequest['quantity'] > 0;
            })
            ->values();

        if ($selectedVehicleRequests->isEmpty()) {
            return back()
                ->withErrors(['vehicle_requests' => 'Select at least one vehicle type and quantity.'])
                ->withInput();
        }

        $availableVehicleInventory = $this->resolveAvailableVehicleInventory();
        $availableVehicleTypes = $availableVehicleInventory['types'];
        $availableVehicleCounts = $availableVehicleInventory['counts'];

        $requestedVehicleTypes = $selectedVehicleRequests->pluck('type')->unique();
        $hasUnavailableVehicle = $requestedVehicleTypes->contains(function (string $type) use ($availableVehicleTypes) {
            return empty($availableVehicleTypes[$type]);
        });

        if ($hasUnavailableVehicle) {
            return back()
                ->withErrors(['vehicle_requests' => 'One or more selected vehicles are currently unavailable.'])
                ->withInput();
        }

        foreach ($selectedVehicleRequests as $vehicleRequest) {
            $vehicleType = (string) ($vehicleRequest['type'] ?? '');
            $requestedQuantity = (int) ($vehicleRequest['quantity'] ?? 0);
            $maxAllowed = (int) ($availableVehicleCounts[$vehicleType] ?? 0);

            if ($requestedQuantity > $maxAllowed) {
                return back()
                    ->withErrors([
                        'vehicle_requests' => 'Requested quantity for ' . strtoupper($vehicleType) . ' exceeds available units (' . $maxAllowed . ').',
                    ])
                    ->withInput();
            }
        }

        $vehicleSummary = $selectedVehicleRequests
            ->map(function (array $vehicleRequest) {
                return $vehicleRequest['type'] . ':' . $vehicleRequest['quantity'];
            })
            ->implode(', ');

        $vehicleTotalQuantity = (int) $selectedVehicleRequests->sum('quantity');

        $storedAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $path = $attachment->store('request_attachments', 'public');
                $storedAttachments[] = [
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_path' => $path,
                ];
            }
        }

        $user = Auth::user();

        $transportationRequest = TransportationRequestFormModel::create([
            'form_id' => 'REQ-' . now()->format('Y') . '-' . strtoupper(Str::random(4)),
            'form_creator_id' => $user->personnel_id,
            'request_date' => $validated['request_date'],
            'requested_by' => $validated['requested_by'],
            'destination' => $validated['destination'],
            'date_time_from' => $validated['date_time_from'],
            'date_time_to' => $validated['date_time_to'],
            'purpose' => $purposeWithPassengers,
            'vehicle_type' => $vehicleSummary,
            'vehicle_quantity' => $vehicleTotalQuantity,
            'business_passengers' => $businessPassengers,
            'division_personnel' => [[
                'name' => $validated['requesting_division_name'],
                'position' => $validated['requesting_division_position'],
            ]],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'driver_name' => $validated['driver_name'] ?? null,
            'attachments' => $storedAttachments,
            'status' => 'To be Signed',
        ]);

        // For the Logic of the Excel Form 05/ Transportation Request Form

        $templatePath = storage_path('app/public/forms/form_05_Transportation_Request_rev_08.xlsx');
        if (!is_readable($templatePath)) {
            return redirect()
                ->route('request-form')
                ->with('error', 'Transportation request was saved, but the spreadsheet template file could not be found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Data's

        $divisionManager = AssignatoryPersonnelResolver::resolve()['name'];

        // Date Cell Activation

        // Date of the Request Travel
        $daterow = 10;

        $sheet->mergeCells('H10:I10');
        $sheet->setCellValue('H10', $validated['request_date']);

        $sheet->mergeCells('C12:J12');
        $sheet->setCellValue('C12', $validated['requested_by']);

        // Purpose of the Request Travel
        // Wrap long text into multiple rows starting at row 13.
        $purposeLines = preg_split('/\r\n|\r|\n/', wordwrap($purposeWithPassengers, 85));

        // Template allocates rows 13-15 for purpose.
        // If purpose needs more lines, push the rows below (destination/date-time) down.
        $basePurposeLines = 3;
        $extraPurposeLines = max(0, count($purposeLines) - $basePurposeLines);
        if ($extraPurposeLines > 0) {
            $sheet->insertNewRowBefore(16, $extraPurposeLines);
        }

        $row = 13;
        foreach ($purposeLines as $line) {
            $sheet->mergeCells("C{$row}:J{$row}");
            $sheet->setCellValue("C{$row}", $line);
            $row++;
        }

        $destinationRow = 16 + $extraPurposeLines;
        $dateTimeUsedRow = 17 + $extraPurposeLines;

        $sheet->mergeCells("C{$destinationRow}:J{$destinationRow}");
        $sheet->setCellValue("C{$destinationRow}", $validated['destination']);

        $sheet->mergeCells("C{$dateTimeUsedRow}:J{$dateTimeUsedRow}");
        $sheet->setCellValue(
            "C{$dateTimeUsedRow}",
            $validated['date_time_from'] . ' to ' . $validated['date_time_to']
        );

        // Requesting Personnel's Name and Position
        $requestingDivisionRow = 20 + $extraPurposeLines;
        $sheet->mergeCells("C{$requestingDivisionRow}:F{$requestingDivisionRow}");
        $sheet->setCellValue("C{$requestingDivisionRow}", $validated['requesting_division_name']);

        $sheet->mergeCells("G{$requestingDivisionRow}:J{$requestingDivisionRow}");
        $sheet->setCellValue("G{$requestingDivisionRow}", $validated['requesting_division_position']);


        // Plate Number and Driver's Name
        $vehicleInfoRow = 23 + $extraPurposeLines;
        $sheet->mergeCells("E{$vehicleInfoRow}:F{$vehicleInfoRow}");
        $sheet->setCellValue("E{$vehicleInfoRow}", $validated['vehicle_id'] ?? 'N/A');

        $sheet->mergeCells("H{$vehicleInfoRow}:J{$vehicleInfoRow}");
        $sheet->setCellValue("H{$vehicleInfoRow}", $validated['driver_name'] ?? 'N/A');


        // Division Manager's Name
        $divisionManagerRow = 28 + $extraPurposeLines;
        $sheet->mergeCells("G{$divisionManagerRow}:I{$divisionManagerRow}");
        $sheet->setCellValue("G{$divisionManagerRow}", $divisionManager);

        //For the Process of Excel Download File

        $outputDirectory = storage_path('app/public/generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        try {
            $writer = new Xlsx($spreadsheet);
            $filename = 'Transportation_Request_Form_' . now()->format('Ymd_His') . '.xlsx';
            $tempPath = $outputDirectory . DIRECTORY_SEPARATOR . $filename;
            $writer->save($tempPath);

            $transportationRequest->update([
                'generated_filename' => $filename,
            ]);

            $transportationRequest->upsertAttachment([
                'file_name' => $filename,
                'file_path' => 'generated_forms/' . $filename,
                'process' => 'transportation_request_form',
                'process_key' => self::REQUEST_FORM_ATTACHMENT_KEY,
                'source' => 'request_form_generated',
            ]);

            if ($request->boolean('download_request_form')) {
                return response()->download($tempPath, $filename);
            }

            return redirect()
                ->route('request-form')
                ->with('request_form_success', 'Transportation request download successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('request-form')
                ->with('error', 'Transportation request was saved, but spreadsheet generation failed. Please contact support.');
        }
    }

    public function downloadGeneratedForm(string $filename)
    {
        $safeFilename = basename($filename);
        $path = storage_path('app/public/generated_forms/' . $safeFilename);

        if (pathinfo($safeFilename, PATHINFO_EXTENSION) !== 'xlsx' || !is_readable($path)) {
            return redirect()
                ->route('request-form')
                ->with('error', 'The requested spreadsheet file is unavailable. Please submit the form again.');
        }

        return response()->download($path, $safeFilename);
    }

    public function personnelLookup(string $personnelId)
    {
        if (!preg_match('/^\d{6}$/', $personnelId)) {
            return response()->json(['message' => 'Invalid personnel ID format.'], 422);
        }

        $user = User::query()
            ->select('personnel_id', 'name')
            ->where('personnel_id', $personnelId)
            ->where('personnel_id', '!=', self::SYSADMIN_PERSONNEL_ID)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Personnel not found.'], 404);
        }

        return response()->json([
            'personnel_id' => $user->personnel_id,
            'name' => $user->name,
        ]);
    }

    public function viewOwnAttachment(TransportationRequestFormModel $transportationRequest, int $index)
    {
        $user = Auth::user();

        if (!$user || (string) $transportationRequest->form_creator_id !== (string) $user->personnel_id) {
            abort(403);
        }

        $attachments = $transportationRequest->normalizeAttachments();

        if (!array_key_exists($index, $attachments)) {
            abort(404);
        }

        $attachment = $attachments[$index];
        $relativePath = $attachment['file_path'] ?? null;

        if (!$relativePath || !Storage::disk('public')->exists($relativePath)) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($relativePath);
        $filename = $attachment['file_name'] ?? basename($relativePath);

        return response()->file($absolutePath, [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function resolveAvailableVehicleInventory(): array
    {
        $vehicleTypeCounts = [
            'coaster' => 0,
            'van' => 0,
            'pickup' => 0,
        ];

        $availableVehicles = AdminVehicleAvailability::query()
            ->get(['vehicle_type', 'capacity_label']);

        $capacityMap = [
            'coaster' => null,
            'van' => null,
            'pickup' => null,
        ];

        foreach ($availableVehicles as $availableVehicle) {
            $label = strtolower((string) ($availableVehicle->vehicle_type ?? ''));
            $capacityLabel = trim((string) ($availableVehicle->capacity_label ?? '')) ?: null;

            if (str_contains($label, 'coaster')) {
                $vehicleTypeCounts['coaster']++;
                if ($capacityMap['coaster'] === null && $capacityLabel !== null) {
                    $capacityMap['coaster'] = $capacityLabel;
                }
            }

            if (str_contains($label, 'van')) {
                $vehicleTypeCounts['van']++;
                if ($capacityMap['van'] === null && $capacityLabel !== null) {
                    $capacityMap['van'] = $capacityLabel;
                }
            }

            if (str_contains($label, 'pickup') || str_contains($label, 'pick-up')) {
                $vehicleTypeCounts['pickup']++;
                if ($capacityMap['pickup'] === null && $capacityLabel !== null) {
                    $capacityMap['pickup'] = $capacityLabel;
                }
            }
        }

        return [
            'counts' => $vehicleTypeCounts,
            'types' => [
                'coaster' => $vehicleTypeCounts['coaster'] > 0,
                'van' => $vehicleTypeCounts['van'] > 0,
                'pickup' => $vehicleTypeCounts['pickup'] > 0,
            ],
            'capacities' => $capacityMap,
        ];
    }
}
