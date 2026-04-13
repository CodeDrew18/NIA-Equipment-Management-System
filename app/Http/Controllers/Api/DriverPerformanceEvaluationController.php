<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverPerformanceEvaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverPerformanceEvaluationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $driverUser = $request->user();
        $driverName = trim((string) ($driverUser?->name ?? ''));

        if ($driverName === '') {
            return response()->json([
                'message' => 'Authenticated user has no name configured.',
                'data' => [],
            ], 422);
        }

        $normalizedDriverName = $this->normalizeName($driverName);

        $evaluations = DriverPerformanceEvaluation::query()
            ->with([
                'transportationRequestForm:id,form_id,request_date,destination,date_time_from,date_time_to,vehicle_id,vehicle_type,driver_name,status',
            ])
            ->where('status', 'Submitted')
            ->where(function ($query) use ($normalizedDriverName) {
                $query->whereRaw('LOWER(TRIM(driver_name)) = ?', [$normalizedDriverName])
                    ->orWhereHas('transportationRequestForm', function ($requestQuery) use ($normalizedDriverName) {
                        $requestQuery->whereRaw('LOWER(TRIM(driver_name)) = ?', [$normalizedDriverName]);
                    });
            })
            ->orderByDesc('evaluated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(function (DriverPerformanceEvaluation $evaluation) use ($normalizedDriverName) {
                return $this->evaluationBelongsToDriver($evaluation, $normalizedDriverName);
            })
            ->values();

        return response()->json([
            'message' => 'Driver performance evaluations fetched successfully.',
            'driver' => [
                'id' => $driverUser->id,
                'personnel_id' => $driverUser->personnel_id,
                'name' => $driverUser->name,
                'role' => $driverUser->role,
            ],
            'total' => $evaluations->count(),
            'data' => $evaluations->map(function (DriverPerformanceEvaluation $evaluation) {
                return $this->mapEvaluationPayload($evaluation);
            })->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $driverUser = $request->user();
        $driverName = trim((string) ($driverUser?->name ?? ''));

        if ($driverName === '') {
            return response()->json([
                'message' => 'Authenticated user has no name configured.',
                'data' => [],
            ], 422);
        }

        $evaluation = DriverPerformanceEvaluation::query()
            ->with([
                'transportationRequestForm:id,form_id,request_date,destination,date_time_from,date_time_to,vehicle_id,vehicle_type,driver_name,status',
            ])
            ->find($id);

        if (!$evaluation) {
            return response()->json([
                'message' => 'Driver performance evaluation not found.',
            ], 404);
        }

        if ((string) ($evaluation->status ?? '') !== 'Submitted') {
            return response()->json([
                'message' => 'This driver performance evaluation is not yet submitted.',
            ], 403);
        }

        $normalizedDriverName = $this->normalizeName($driverName);
        if (!$this->evaluationBelongsToDriver($evaluation, $normalizedDriverName)) {
            return response()->json([
                'message' => 'You are not allowed to access this driver performance evaluation.',
            ], 403);
        }

        return response()->json([
            'message' => 'Driver performance evaluation fetched successfully.',
            'data' => $this->mapEvaluationPayload($evaluation),
        ]);
    }

    private function mapEvaluationPayload(DriverPerformanceEvaluation $evaluation): array
    {
        $requestForm = $evaluation->transportationRequestForm;
        $evaluationPayload = is_array($evaluation->evaluation_payload)
            ? $evaluation->evaluation_payload
            : [];
        $attachment = is_array($evaluation->attachment)
            ? $evaluation->attachment
            : [];

        $attachmentPath = trim((string) ($attachment['file_path'] ?? ''));
        $attachmentUrl = $attachmentPath !== ''
            ? Storage::url($attachmentPath)
            : null;

        return [
            'id' => $evaluation->id,
            'transportation_request_form_id' => $evaluation->transportation_request_form_id,
            'copy_key' => (string) ($evaluation->copy_key ?? ''),
            'copy_number' => $evaluation->copy_number,
            'status' => (string) ($evaluation->status ?? ''),
            'driver_name' => (string) ($evaluation->driver_name ?? ''),
            'evaluator_name' => (string) ($evaluation->evaluator_name ?? ''),
            'overall_rating' => $evaluation->overall_rating,
            'timeliness_score' => $evaluation->timeliness_score,
            'safety_score' => $evaluation->safety_score,
            'compliance_score' => $evaluation->compliance_score,
            'comments' => (string) ($evaluation->comments ?? ''),
            'evaluation_payload' => $evaluationPayload,
            'attachment' => [
                'file_name' => (string) ($attachment['file_name'] ?? ''),
                'file_path' => $attachmentPath,
                'file_url' => $attachmentUrl,
                'process' => (string) ($attachment['process'] ?? ''),
                'process_key' => (string) ($attachment['process_key'] ?? ''),
                'source' => (string) ($attachment['source'] ?? ''),
                'copy_key' => (string) ($attachment['copy_key'] ?? ''),
            ],
            'evaluated_at' => optional($evaluation->evaluated_at)->toDateTimeString(),
            'created_at' => optional($evaluation->created_at)->toDateTimeString(),
            'updated_at' => optional($evaluation->updated_at)->toDateTimeString(),
            'request' => [
                'id' => $requestForm?->id,
                'form_id' => (string) ($requestForm?->form_id ?? ''),
                'request_date' => optional($requestForm?->request_date)->toDateString(),
                'destination' => (string) ($requestForm?->destination ?? ''),
                'date_time_from' => optional($requestForm?->date_time_from)->toDateTimeString(),
                'date_time_to' => optional($requestForm?->date_time_to)->toDateTimeString(),
                'vehicle_id' => (string) ($requestForm?->vehicle_id ?? ''),
                'vehicle_type' => (string) ($requestForm?->vehicle_type ?? ''),
                'status' => (string) ($requestForm?->status ?? ''),
            ],
        ];
    }

    private function evaluationBelongsToDriver(DriverPerformanceEvaluation $evaluation, string $normalizedDriverName): bool
    {
        if ($normalizedDriverName === '') {
            return false;
        }

        $evaluationDriverNames = $this->parseDriverNames((string) ($evaluation->driver_name ?? ''));
        if ($this->normalizedNamesContain($evaluationDriverNames, $normalizedDriverName)) {
            return true;
        }

        $requestDriverNames = $this->parseDriverNames((string) ($evaluation->transportationRequestForm?->driver_name ?? ''));

        return $this->normalizedNamesContain($requestDriverNames, $normalizedDriverName);
    }

    private function normalizedNamesContain(array $names, string $needle): bool
    {
        return collect($names)
            ->map(function (string $name) {
                return $this->normalizeName($name);
            })
            ->contains(function (string $name) use ($needle) {
                return $name === $needle;
            });
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

    private function normalizeName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''));
    }
}
