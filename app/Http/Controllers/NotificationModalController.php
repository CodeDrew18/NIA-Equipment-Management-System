<?php

namespace App\Http\Controllers;

use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationModalController extends Controller
{
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
