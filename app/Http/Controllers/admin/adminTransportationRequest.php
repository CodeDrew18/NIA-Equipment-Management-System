<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class adminTransportationRequest extends Controller
{
    private const STATUS_OPTIONS = ['To be Signed', 'Signed', 'Rejected', 'Cancelled'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $requests = TransportationRequestFormModel::query()
            ->where('status', 'To be Signed')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
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
            $requestItem->setAttribute(
                'normalized_attachments',
                $this->normalizeAttachments($requestItem->attachments)
            );

            return $requestItem;
        });

        return view('admin.transportation_request.admin_transportation_request', [
            'requests' => $requests,
            'search' => $search,
        ]);
    }

    public function updateStatus(Request $request, TransportationRequestFormModel $transportationRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
            'rejection_reason' => ['nullable', 'string', 'max:2000', 'required_if:status,Rejected', 'required_if:status,Cancelled'],
        ]);

        $previousVehicleCodes = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);

        $updatePayload = [
            'status' => $validated['status'],
            'rejection_reason' => in_array($validated['status'], ['Rejected', 'Cancelled'], true)
                ? trim((string) ($validated['rejection_reason'] ?? ''))
                : null,
        ];

        // Approval must always pass through admin vehicle assignment first.
        if (in_array($validated['status'], ['Signed', 'Rejected', 'Cancelled'], true)) {
            $updatePayload['vehicle_id'] = null;
            $updatePayload['driver_name'] = null;
        }

        DB::transaction(function () use ($transportationRequest, $updatePayload, $previousVehicleCodes) {
            $transportationRequest->update($updatePayload);
            $this->releaseVehicleCodes($previousVehicleCodes);
        });

        if ($validated['status'] === 'Signed') {
            return redirect()
                ->route('admin.vehicle_assignment', ['request' => $transportationRequest->id])
                ->with('admin_vehicle_assignment_success', 'Request approved. Assign an available vehicle before generating the Daily Driver\'s Trip Ticket.');
        }

        return redirect()
            ->route('admin.transportation-request')
            ->with('admin_transportation_request_success', 'Request status updated to ' . $validated['status'] . '.');
    }

    public function viewAttachment(TransportationRequestFormModel $transportationRequest, int $index)
    {
        $attachments = $this->normalizeAttachments($transportationRequest->attachments);

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

    private function releaseVehicleCodes(array $vehicleCodes): void
    {
        if (empty($vehicleCodes)) {
            return;
        }

        AdminVehicleAvailability::query()
            ->whereIn('vehicle_code', $vehicleCodes)
            ->whereIn('status', ['On Business Trip', 'Reserved'])
            ->update([
                'status' => 'Available',
            ]);
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

    private function normalizeAttachments(mixed $attachments): array
    {
        if (is_string($attachments) && trim($attachments) !== '') {
            $decoded = json_decode($attachments, true);
            if (is_array($decoded)) {
                $attachments = $decoded;
            }
        }

        if (!is_array($attachments)) {
            return [];
        }

        return collect($attachments)
            ->map(function ($attachment) {
                if (is_string($attachment)) {
                    return [
                        'file_name' => basename($attachment),
                        'file_path' => $attachment,
                    ];
                }

                if (is_array($attachment)) {
                    $filePath = trim((string) ($attachment['file_path'] ?? ''));
                    $fileName = trim((string) ($attachment['file_name'] ?? basename($filePath)));

                    return [
                        'file_name' => $fileName !== '' ? $fileName : 'Attachment',
                        'file_path' => $filePath,
                    ];
                }

                return null;
            })
            ->filter(function ($attachment) {
                return is_array($attachment) && !empty($attachment['file_path']);
            })
            ->values()
            ->all();
    }
}
