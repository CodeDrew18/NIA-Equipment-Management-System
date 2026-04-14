<?php

namespace App\Http\Controllers;

use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationModalController extends Controller
{
    public function userReturnedRequests(Request $request): JsonResponse
    {
        $personnelId = (string) ($request->user()?->personnel_id ?? '');

        if ($personnelId === '') {
            return response()->json([
                'latestRequest' => null,
                'latestRequestId' => 0,
                'latestRequestSignature' => '',
            ]);
        }

        $latestReturnedRequest = TransportationRequestFormModel::query()
            ->where('form_creator_id', $personnelId)
            ->where('status', 'Rejected')
            ->whereNotNull('rejection_reason')
            ->where('rejection_reason', '!=', '')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first([
                'id',
                'form_id',
                'rejection_reason',
                'attachments',
                'updated_at',
            ]);

        if (!$latestReturnedRequest) {
            return response()->json([
                'latestRequest' => null,
                'latestRequestId' => 0,
                'latestRequestSignature' => '',
            ]);
        }

        $attachments = collect(is_array($latestReturnedRequest->attachments) ? $latestReturnedRequest->attachments : [])
            ->values()
            ->map(function ($attachment, int $index) use ($latestReturnedRequest): array {
                $fileName = '';
                if (is_array($attachment)) {
                    $fileName = trim((string) ($attachment['file_name'] ?? ''));
                }

                return [
                    'fileName' => $fileName !== '' ? $fileName : 'Attachment',
                    'url' => route('request-form.attachment.view', [
                        'transportationRequest' => $latestReturnedRequest->id,
                        'index' => $index,
                    ]),
                ];
            })
            ->all();

        $latestRequestId = (int) ($latestReturnedRequest->id ?? 0);
        $latestRequestSignature = implode('|', [
            (string) $latestRequestId,
            (string) (optional($latestReturnedRequest->updated_at)->timestamp ?? 0),
        ]);

        return response()->json([
            'latestRequest' => [
                'id' => $latestRequestId,
                'formId' => (string) ($latestReturnedRequest->form_id ?? 'N/A'),
                'rejectionReason' => (string) ($latestReturnedRequest->rejection_reason ?? ''),
                'attachments' => $attachments,
            ],
            'latestRequestId' => $latestRequestId,
            'latestRequestSignature' => $latestRequestSignature,
        ]);
    }

    public function userPendingEvaluations(Request $request): JsonResponse
    {
        $personnelId = (string) ($request->user()?->personnel_id ?? '');

        if ($personnelId === '') {
            return response()->json([
                'pendingCount' => 0,
                'pendingSignature' => '',
            ]);
        }

        $pendingEvaluationsQuery = DriverPerformanceEvaluation::query()
            ->where('status', 'Pending')
            ->whereHas('transportationRequestForm', function (Builder $query) use ($personnelId) {
                $query->where('form_creator_id', $personnelId)
                    ->where('status', 'For Evaluation');
            });

        $pendingCount = (clone $pendingEvaluationsQuery)->count();
        $pendingSignature = '';

        if ($pendingCount > 0) {
            $latestPendingEvaluation = (clone $pendingEvaluationsQuery)
                ->select(['id', 'updated_at'])
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            $pendingSignature = implode('|', [
                (string) $pendingCount,
                (string) ($latestPendingEvaluation?->id ?? 0),
                (string) (optional($latestPendingEvaluation?->updated_at)->timestamp ?? 0),
            ]);
        }

        return response()->json([
            'pendingCount' => (int) $pendingCount,
            'pendingSignature' => (string) $pendingSignature,
        ]);
    }

    public function adminPendingTransportationRequests(): JsonResponse
    {
        $pendingTransportationRequestsQuery = TransportationRequestFormModel::query()
            ->whereIn('status', ['To be Signed', 'Pending']);

        $pendingCount = (clone $pendingTransportationRequestsQuery)->count();
        $pendingSignature = '';

        if ($pendingCount > 0) {
            $latestPendingTransportationRequest = (clone $pendingTransportationRequestsQuery)
                ->select(['id', 'updated_at'])
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            $pendingSignature = implode('|', [
                (string) $pendingCount,
                (string) ($latestPendingTransportationRequest?->id ?? 0),
                (string) (optional($latestPendingTransportationRequest?->updated_at)->timestamp ?? 0),
            ]);
        }

        return response()->json([
            'pendingCount' => (int) $pendingCount,
            'pendingSignature' => (string) $pendingSignature,
        ]);
    }
}
