<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class requestFormController extends Controller
{
    public function requestForm()
    {
        return view('letter_of_request/requestform');
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
            'division_personnel.*.id_number' => ['required', 'digits:6', 'exists:users,personnel_id'],
            'division_personnel.*.name' => ['required', 'string', 'max:255'],
            'vehicle_id' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

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

        TransportationRequestFormModel::create([
            'form_id' => 'REQ-' . now()->format('Y') . '-' . strtoupper(Str::random(4)),
            'request_date' => $validated['request_date'],
            'requested_by' => $validated['requested_by'],
            'destination' => $validated['destination'],
            'date_time_from' => $validated['date_time_from'],
            'date_time_to' => $validated['date_time_to'],
            'purpose' => $validated['purpose'],
            'vehicle_type' => $vehicleSummary,
            'vehicle_quantity' => $vehicleTotalQuantity,
            'division_personnel' => $validated['division_personnel'],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'driver_name' => $validated['driver_name'] ?? null,
            'attachments' => $storedAttachments,
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

        // Date Cell Activation
        $daterow = 10;

        $sheet->mergeCells('H10:I10');
        $sheet->setCellValue('H10', $validated['request_date']);

        $sheet->mergeCells('C12:J12');
        $sheet->setCellValue('C12', $validated['requested_by']);

        for ($row = 13; $row <= 15; $row++) {
            $sheet->mergeCells("C{$row}:J{$row}");
            $sheet->setCellValue("C{$row}", $validated['purpose']);
        }



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

            return redirect()
                ->route('request-form')
                ->with('success', 'Transportation request download successfully.')
                ->with('download_file', $filename)
                ->with('auto_download', true);
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

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function personnelLookup(string $personnelId)
    {
        if (!preg_match('/^\d{6}$/', $personnelId)) {
            return response()->json(['message' => 'Invalid personnel ID format.'], 422);
        }

        $user = User::query()
            ->select('personnel_id', 'name')
            ->where('personnel_id', $personnelId)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Personnel not found.'], 404);
        }

        return response()->json([
            'personnel_id' => $user->personnel_id,
            'name' => $user->name,
        ]);
    }
}
