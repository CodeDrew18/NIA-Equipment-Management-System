<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\TransportationRequestFormModel;
use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class vehicleAssignmentController extends Controller
{
    private const VEHICLE_TYPES = ['coaster', 'van', 'pickup', 'other'];
    private const REQUEST_FORM_ATTACHMENT_KEY = 'transportation_request_form_file';
    private const DIVISION_MANAGER = 'ENGR. EMILIO M. DOMAGAS JR.';

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $selectedRequestId = (int) $request->query('request', 0);

        $requests = TransportationRequestFormModel::query()
            ->where('status', 'Signed')
            ->where(function (Builder $query) {
                $query->whereNull('vehicle_id')
                    ->orWhere('vehicle_id', '')
                    ->orWhereNull('driver_name')
                    ->orWhere('driver_name', '');
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('form_id', 'like', '%' . $search . '%')
                        ->orWhere('requested_by', 'like', '%' . $search . '%')
                        ->orWhere('destination', 'like', '%' . $search . '%')
                        ->orWhere('vehicle_type', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $requests->getCollection()->transform(function (TransportationRequestFormModel $requestItem) {
            $requestedVehicleMix = $this->parseRequestedVehicleMix(
                (string) $requestItem->vehicle_type,
                (int) ($requestItem->vehicle_quantity ?? 0)
            );

            $requestItem->setAttribute('requested_vehicle_mix', $requestedVehicleMix);
            $requestItem->setAttribute('required_vehicle_total', array_sum($requestedVehicleMix));

            return $requestItem;
        });

        $availableVehicles = AdminVehicleAvailability::query()
            ->where('status', 'Available')
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_code')
            ->get();

        $groupedVehicles = [
            'coaster' => collect(),
            'van' => collect(),
            'pickup' => collect(),
            'other' => collect(),
        ];

        foreach ($availableVehicles as $vehicle) {
            $type = $this->normalizeVehicleType((string) $vehicle->vehicle_type);
            $groupedVehicles[$type] = $groupedVehicles[$type]->push($vehicle);
        }

        return view('admin.vehicle_assignment.vehicle_assignment', [
            'requests' => $requests,
            'search' => $search,
            'selectedRequestId' => $selectedRequestId,
            'availableVehicles' => $availableVehicles,
            'availableVehiclesByType' => $groupedVehicles,
            'availableVehicleCounts' => [
                'all' => $availableVehicles->count(),
                'coaster' => $groupedVehicles['coaster']->count(),
                'van' => $groupedVehicles['van']->count(),
                'pickup' => $groupedVehicles['pickup']->count(),
            ],
        ]);
    }

    public function assign(Request $request, TransportationRequestFormModel $transportationRequest, FcmPushService $fcmPushService)
    {
        $previousVehicleCodes = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);

        $validated = $request->validate([
            'assignment_action' => ['required', 'in:assign_vehicles'],
            'vehicle_codes' => ['required', 'array'],
            'vehicle_codes.*' => ['array'],
            'vehicle_codes.*.*' => ['nullable', 'string', 'max:255'],
        ]);

        if ((string) $transportationRequest->status !== 'Signed') {
            throw ValidationException::withMessages([
                'vehicle_codes' => 'Only signed requests can be assigned to vehicles.',
            ]);
        }

        $requestedVehicleMix = $this->parseRequestedVehicleMix(
            (string) $transportationRequest->vehicle_type,
            (int) ($transportationRequest->vehicle_quantity ?? 0)
        );
        $requiredVehicleTotal = array_sum($requestedVehicleMix);

        $selectedCodesByType = collect(self::VEHICLE_TYPES)
            ->mapWithKeys(function (string $type) use ($validated) {
                $codes = $validated['vehicle_codes'][$type] ?? [];

                if (!is_array($codes)) {
                    return [$type => []];
                }

                $normalizedCodes = collect($codes)
                    ->map(function ($code) {
                        return trim((string) $code);
                    })
                    ->filter(function (string $code) {
                        return $code !== '';
                    })
                    ->values()
                    ->all();

                return [$type => $normalizedCodes];
            })
            ->all();

        foreach ($requestedVehicleMix as $type => $requiredCount) {
            $selectedCount = count($selectedCodesByType[$type] ?? []);

            if ($selectedCount !== $requiredCount) {
                throw ValidationException::withMessages([
                    'vehicle_codes' => 'Select exactly ' . $requiredCount . ' ' . $this->vehicleTypeLabel($type) . ' vehicle' . ($requiredCount === 1 ? '' : 's') . '.',
                ]);
            }
        }

        $selectedVehicleCodes = collect($selectedCodesByType)->flatten(1)->values()->all();

        if (count($selectedVehicleCodes) !== $requiredVehicleTotal) {
            throw ValidationException::withMessages([
                'vehicle_codes' => 'Select exactly ' . $requiredVehicleTotal . ' vehicle' . ($requiredVehicleTotal === 1 ? '' : 's') . ' before continuing.',
            ]);
        }

        if (count(array_unique($selectedVehicleCodes)) !== count($selectedVehicleCodes)) {
            throw ValidationException::withMessages([
                'vehicle_codes' => 'Duplicate vehicles are not allowed. Select a different vehicle for each slot.',
            ]);
        }

        $vehiclesByCode = AdminVehicleAvailability::query()
            ->whereIn('vehicle_code', $selectedVehicleCodes)
            ->get()
            ->keyBy('vehicle_code');

        if ($vehiclesByCode->count() !== count($selectedVehicleCodes)) {
            throw ValidationException::withMessages([
                'vehicle_codes' => 'One or more selected vehicles do not exist anymore. Refresh and try again.',
            ]);
        }

        foreach ($selectedCodesByType as $type => $vehicleCodes) {
            foreach ($vehicleCodes as $vehicleCode) {
                $vehicle = $vehiclesByCode->get($vehicleCode);

                if (!$vehicle) {
                    throw ValidationException::withMessages([
                        'vehicle_codes' => 'Selected vehicle does not exist.',
                    ]);
                }

                if ((string) $vehicle->status !== 'Available') {
                    throw ValidationException::withMessages([
                        'vehicle_codes' => 'Vehicle ' . $vehicle->vehicle_code . ' is no longer available.',
                    ]);
                }

                $driverName = trim((string) $vehicle->driver_name);
                if ($driverName === '') {
                    throw ValidationException::withMessages([
                        'vehicle_codes' => 'Vehicle ' . $vehicle->vehicle_code . ' has no assigned driver. Update Vehicle Availability first.',
                    ]);
                }

                $vehicleType = $this->normalizeVehicleType((string) $vehicle->vehicle_type);
                if ($type !== 'other' && $vehicleType !== $type) {
                    throw ValidationException::withMessages([
                        'vehicle_codes' => 'Vehicle ' . $vehicle->vehicle_code . ' does not match the requested ' . $this->vehicleTypeLabel($type) . ' slot.',
                    ]);
                }
            }
        }

        $driverNames = collect($selectedVehicleCodes)
            ->map(function (string $vehicleCode) use ($vehiclesByCode) {
                return trim((string) optional($vehiclesByCode->get($vehicleCode))->driver_name);
            })
            ->filter()
            ->unique()
            ->values();

        DB::transaction(function () use ($transportationRequest, $selectedVehicleCodes, $driverNames, $previousVehicleCodes) {
            $vehicleCodesToRelease = array_values(array_diff($previousVehicleCodes, $selectedVehicleCodes));

            $transportationRequest->update([
                'vehicle_id' => implode(', ', $selectedVehicleCodes),
                'driver_name' => $driverNames->implode(', '),
            ]);

            if (!empty($vehicleCodesToRelease)) {
                AdminVehicleAvailability::query()
                    ->whereIn('vehicle_code', $vehicleCodesToRelease)
                    ->whereIn('status', ['On Business Trip', 'Reserved'])
                    ->update([
                        'status' => 'Available',
                    ]);
            }

            AdminVehicleAvailability::query()
                ->whereIn('vehicle_code', $selectedVehicleCodes)
                ->update([
                    'status' => 'Reserved',
                ]);
        });

        try {
            $transportationRequest->refresh();
            $this->storeAssignedRequestFormAttachment($transportationRequest);
        } catch (\Throwable $exception) {
            Log::warning('Failed updating transportation request attachment after assignment.', [
                'transportation_request_form_id' => $transportationRequest->id,
                'error' => $exception->getMessage(),
            ]);
        }

        $notifiedDrivers = $this->sendAssignmentPushToDrivers(
            $transportationRequest,
            $driverNames->all(),
            $fcmPushService
        );

        $successMessage = 'Assigned ' . count($selectedVehicleCodes) . ' vehicle' . (count($selectedVehicleCodes) === 1 ? '' : 's') . ' to ' . $transportationRequest->form_id . '. You can now process the Daily Driver\'s Trip Ticket.';

        if ($notifiedDrivers > 0) {
            $successMessage .= ' Push sent to ' . $notifiedDrivers . ' driver' . ($notifiedDrivers === 1 ? '' : 's') . '.';
        } else {
            $successMessage .= ' No push sent (missing FCM token or unmatched driver account name).';
        }

        return redirect()
            ->route('admin.daily-trip-ticket')
            ->with('admin_dtt_success', $successMessage);
    }

    private function sendAssignmentPushToDrivers(TransportationRequestFormModel $transportationRequest, array $assignedDriverNames, FcmPushService $fcmPushService): int
    {
        $normalizedDriverNames = collect($assignedDriverNames)
            ->map(function ($name) {
                return $this->normalizePersonName((string) $name);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($normalizedDriverNames)) {
            Log::info('Assignment push skipped because no normalized driver names were resolved.', [
                'transportation_request_form_id' => $transportationRequest->id,
            ]);

            return 0;
        }

        $matchedUsers = User::query()
            ->select(['id', 'name', 'fcm_token'])
            ->get()
            ->filter(function (User $user) use ($normalizedDriverNames) {
                $normalizedUserName = $this->normalizePersonName((string) ($user->name ?? ''));

                return in_array($normalizedUserName, $normalizedDriverNames, true);
            })
            ->values();

        if ($matchedUsers->isEmpty()) {
            Log::warning('Assignment push skipped because no matching user account names were found.', [
                'transportation_request_form_id' => $transportationRequest->id,
                'assigned_driver_names' => $assignedDriverNames,
            ]);

            return 0;
        }

        $driverUsers = $matchedUsers
            ->filter(function (User $user) {
                return trim((string) ($user->fcm_token ?? '')) !== '';
            })
            ->values();

        if ($driverUsers->isEmpty()) {
            Log::warning('Assignment push skipped because matched drivers have no registered FCM tokens.', [
                'transportation_request_form_id' => $transportationRequest->id,
                'matched_driver_user_ids' => $matchedUsers->pluck('id')->values()->all(),
            ]);

            return 0;
        }

        $pushedCount = 0;

        $divisionPersonnelNames = $this->resolveDivisionPersonnelNames(
            $transportationRequest->division_personnel,
            (string) ($transportationRequest->requested_by ?? '')
        );
        $purpose = $this->normalizeInlineText((string) ($transportationRequest->purpose ?? ''));
        $dateTimeTo = $this->formatDateTimeForNotification($transportationRequest->date_time_to);

        $notificationTitle = 'New Transportation Request';
        $notificationBody = implode("\n", [
            'Division Personnel: ' . $divisionPersonnelNames,
            'Purpose: ' . Str::limit($purpose !== '' ? $purpose : 'N/A', 140),
            'date_time_to: ' . $dateTimeTo,
        ]);

        foreach ($driverUsers as $driverUser) {
            try {
                $isSent = $fcmPushService->sendToToken(
                    (string) $driverUser->fcm_token,
                    $notificationTitle,
                    $notificationBody,
                    [
                        'transportation_request_form_id' => (string) $transportationRequest->id,
                        'form_id' => (string) ($transportationRequest->form_id ?? ''),
                        'division_personnel_name' => $divisionPersonnelNames,
                        'purpose' => $purpose,
                        'date_time_to' => $dateTimeTo,
                        'type' => 'new_transportation_request',
                    ]
                );

                if ($isSent) {
                    $pushedCount += 1;
                }
            } catch (\Throwable $exception) {
                Log::warning('Failed sending assignment push notification.', [
                    'transportation_request_form_id' => $transportationRequest->id,
                    'driver_user_id' => $driverUser->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $pushedCount;
    }

    private function normalizePersonName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''));
    }

    private function normalizeInlineText(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function resolveDivisionPersonnelNames(mixed $divisionPersonnel, string $fallbackName): string
    {
        $source = $divisionPersonnel;

        if (is_string($source)) {
            $decoded = json_decode($source, true);
            $source = is_array($decoded) ? $decoded : [$source];
        }

        if (!is_array($source)) {
            $source = [];
        }

        $names = collect($source)
            ->map(function ($item) {
                if (is_array($item)) {
                    return $this->normalizeInlineText((string) ($item['name'] ?? ''));
                }

                return $this->normalizeInlineText((string) $item);
            })
            ->filter(function (string $name) {
                return $name !== '';
            })
            ->unique()
            ->values();

        if ($names->isNotEmpty()) {
            return $names->implode(', ');
        }

        $fallback = $this->normalizeInlineText($fallbackName);

        return $fallback !== '' ? $fallback : 'N/A';
    }

    private function formatDateTimeForNotification(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('M d, Y h:i A');
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return 'N/A';
        }

        try {
            return Carbon::parse($raw)->format('M d, Y h:i A');
        } catch (\Throwable) {
            return $raw;
        }
    }

    private function parseRequestedVehicleMix(string $vehicleTypeSummary, int $vehicleQuantity): array
    {
        $mix = [
            'coaster' => 0,
            'van' => 0,
            'pickup' => 0,
            'other' => 0,
        ];

        $segments = preg_split('/\s*,\s*/', strtolower(trim($vehicleTypeSummary)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($segments as $segment) {
            if (preg_match('/^(coaster|van|pickup|pick-up)\s*:\s*(\d{1,2})$/i', $segment, $matches) === 1) {
                $type = $this->normalizeVehicleType((string) $matches[1]);
                $quantity = max(1, (int) $matches[2]);
                $mix[$type] += $quantity;
                continue;
            }

            if (preg_match('/^(coaster|van|pickup|pick-up)$/i', $segment, $matches) === 1) {
                $type = $this->normalizeVehicleType((string) $matches[1]);
                $mix[$type] += 1;
                continue;
            }

            $type = $this->normalizeVehicleType($segment);
            $mix[$type] += 1;
        }

        $parsedTotal = array_sum($mix);
        $expectedTotal = max(1, $vehicleQuantity, $parsedTotal);

        if ($parsedTotal === 0) {
            $mix['other'] = $expectedTotal;
            return $mix;
        }

        if ($parsedTotal < $expectedTotal) {
            $firstKnownType = null;
            foreach (['coaster', 'van', 'pickup'] as $type) {
                if ($mix[$type] > 0) {
                    $firstKnownType = $type;
                    break;
                }
            }

            $typeToIncrement = $firstKnownType ?? 'other';
            $mix[$typeToIncrement] += ($expectedTotal - $parsedTotal);
        }

        return $mix;
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

    private function storeAssignedRequestFormAttachment(TransportationRequestFormModel $transportationRequest): void
    {
        $templatePath = storage_path('app/public/forms/form_05_Transportation_Request_rev_08.xlsx');
        if (!is_readable($templatePath)) {
            throw new \RuntimeException('Transportation request template file not found.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $requestDate = optional($transportationRequest->request_date)->toDateString() ?: '';
        $requestedBy = (string) ($transportationRequest->requested_by ?? '');
        $purpose = trim((string) ($transportationRequest->purpose ?? ''));
        $destination = (string) ($transportationRequest->destination ?? '');
        $dateTimeFrom = optional($transportationRequest->date_time_from)->toDateTimeString();
        $dateTimeTo = optional($transportationRequest->date_time_to)->toDateTimeString();

        $divisionPersonnel = $transportationRequest->division_personnel;
        if (is_string($divisionPersonnel) && trim($divisionPersonnel) !== '') {
            $decodedDivisionPersonnel = json_decode($divisionPersonnel, true);
            if (is_array($decodedDivisionPersonnel)) {
                $divisionPersonnel = $decodedDivisionPersonnel;
            }
        }

        $divisionName = '';
        $divisionPosition = '';

        if (is_array($divisionPersonnel) && isset($divisionPersonnel[0]) && is_array($divisionPersonnel[0])) {
            $divisionName = trim((string) ($divisionPersonnel[0]['name'] ?? ''));
            $divisionPosition = trim((string) ($divisionPersonnel[0]['position'] ?? ''));
        }

        $sheet->mergeCells('H10:I10');
        $sheet->setCellValue('H10', $requestDate);

        $sheet->mergeCells('C12:J12');
        $sheet->setCellValue('C12', $requestedBy);

        $purposeLines = preg_split('/\r\n|\r|\n/', wordwrap($purpose !== '' ? $purpose : 'N/A', 85));
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
        $sheet->setCellValue("C{$destinationRow}", $destination !== '' ? $destination : 'N/A');

        $sheet->mergeCells("C{$dateTimeUsedRow}:J{$dateTimeUsedRow}");
        $sheet->setCellValue(
            "C{$dateTimeUsedRow}",
            trim((string) (($dateTimeFrom ?: 'N/A') . ' to ' . ($dateTimeTo ?: 'N/A')))
        );

        $requestingDivisionRow = 20 + $extraPurposeLines;
        $sheet->mergeCells("C{$requestingDivisionRow}:F{$requestingDivisionRow}");
        $sheet->setCellValue("C{$requestingDivisionRow}", $divisionName !== '' ? $divisionName : 'N/A');

        $sheet->mergeCells("G{$requestingDivisionRow}:J{$requestingDivisionRow}");
        $sheet->setCellValue("G{$requestingDivisionRow}", $divisionPosition !== '' ? $divisionPosition : 'N/A');

        $vehicleInfoRow = 23 + $extraPurposeLines;
        $sheet->mergeCells("E{$vehicleInfoRow}:F{$vehicleInfoRow}");
        $sheet->setCellValue("E{$vehicleInfoRow}", (string) ($transportationRequest->vehicle_id ?: 'N/A'));

        $sheet->mergeCells("H{$vehicleInfoRow}:J{$vehicleInfoRow}");
        $sheet->setCellValue("H{$vehicleInfoRow}", (string) ($transportationRequest->driver_name ?: 'N/A'));

        $divisionManagerRow = 28 + $extraPurposeLines;
        $sheet->mergeCells("G{$divisionManagerRow}:I{$divisionManagerRow}");
        $sheet->setCellValue("G{$divisionManagerRow}", self::DIVISION_MANAGER);

        $outputDirectory = Storage::disk('public')->path('generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $safeFormId = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) ($transportationRequest->form_id ?: 'REQUEST')) ?: 'REQUEST';
        $safeFileName = 'Transportation_Request_Form_' . $safeFormId . '_assigned_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.xlsx';
        $relativePath = 'generated_forms/' . $safeFileName;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $writer = new Xlsx($spreadsheet);
        $writer->save($absolutePath);

        $transportationRequest->update([
            'generated_filename' => $safeFileName,
        ]);

        $transportationRequest->upsertAttachment([
            'file_name' => $safeFileName,
            'file_path' => $relativePath,
            'process' => 'transportation_request_form',
            'process_key' => self::REQUEST_FORM_ATTACHMENT_KEY,
            'source' => 'vehicle_assignment',
        ]);
    }

    private function vehicleTypeLabel(string $type): string
    {
        return match ($type) {
            'coaster' => 'Coaster',
            'van' => 'Van',
            'pickup' => 'Pick-up',
            default => 'Vehicle',
        };
    }

    private function normalizeVehicleType(string $vehicleType): string
    {
        $value = strtolower($vehicleType);

        if (str_contains($value, 'coaster')) {
            return 'coaster';
        }

        if (str_contains($value, 'pickup') || str_contains($value, 'pick-up')) {
            return 'pickup';
        }

        if (str_contains($value, 'van')) {
            return 'van';
        }

        return 'other';
    }
}
