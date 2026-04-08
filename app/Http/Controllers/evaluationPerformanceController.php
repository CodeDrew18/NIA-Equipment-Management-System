<?php

namespace App\Http\Controllers;

use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use App\Models\User;
use App\Notifications\DriverPerformanceEvaluationSubmittedNotification;
use App\Support\TripLifecycleManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class evaluationPerformanceController extends Controller
{
    public function index(Request $request, TripLifecycleManager $tripLifecycleManager)
    {
        $tripLifecycleManager->moveFinishedTripsToEvaluationQueue();

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'evaluation_id' => ['nullable', 'integer'],
            'request_id' => ['nullable', 'integer'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $fromDate = $validated['from'] ?? '';
        $toDate = $validated['to'] ?? '';
        $selectedEvaluationId = isset($validated['evaluation_id']) ? (int) $validated['evaluation_id'] : null;
        $legacySelectedRequestId = isset($validated['request_id']) ? (int) $validated['request_id'] : null;

        $personnelId = (string) (Auth::user()?->personnel_id ?? '');

        $baseQuery = DriverPerformanceEvaluation::query()
            ->with([
                'transportationRequestForm:id,form_id,form_creator_id,request_date,requested_by,destination,date_time_from,date_time_to,purpose,vehicle_type,vehicle_id,driver_name,status,division_personnel',
            ])
            ->where('status', 'Pending')
            ->whereHas('transportationRequestForm', function (Builder $query) use ($personnelId, $fromDate, $toDate) {
                $query->where('form_creator_id', $personnelId)
                    ->where('status', 'For Evaluation');

                if ($fromDate !== '') {
                    $query->whereDate('date_time_to', '>=', $fromDate);
                }

                if ($toDate !== '') {
                    $query->whereDate('date_time_to', '<=', $toDate);
                }
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('driver_name', 'like', '%' . $search . '%')
                        ->orWhereHas('transportationRequestForm', function (Builder $requestQuery) use ($search) {
                            $requestQuery->where('form_id', 'like', '%' . $search . '%')
                                ->orWhere('requested_by', 'like', '%' . $search . '%')
                                ->orWhere('destination', 'like', '%' . $search . '%')
                                ->orWhere('vehicle_id', 'like', '%' . $search . '%')
                                ->orWhere('driver_name', 'like', '%' . $search . '%');
                        });
                });
            });

        $pendingEvaluations = (clone $baseQuery)
            ->orderByDesc('transportation_request_form_id')
            ->orderBy('copy_number')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $selectedEvaluationRecord = null;

        if ($selectedEvaluationId) {
            $selectedEvaluationRecord = DriverPerformanceEvaluation::query()
                ->with([
                    'transportationRequestForm:id,form_id,form_creator_id,request_date,requested_by,destination,date_time_from,date_time_to,purpose,vehicle_type,vehicle_id,driver_name,status,division_personnel',
                ])
                ->whereKey($selectedEvaluationId)
                ->whereHas('transportationRequestForm', function (Builder $query) use ($personnelId) {
                    $query->where('form_creator_id', $personnelId)
                        ->whereIn('status', ['For Evaluation', 'Completed']);
                })
                ->first();
        }

        if (!$selectedEvaluationRecord && $legacySelectedRequestId) {
            $selectedEvaluationRecord = DriverPerformanceEvaluation::query()
                ->with([
                    'transportationRequestForm:id,form_id,form_creator_id,request_date,requested_by,destination,date_time_from,date_time_to,purpose,vehicle_type,vehicle_id,driver_name,status,division_personnel',
                ])
                ->where('transportation_request_form_id', $legacySelectedRequestId)
                ->whereHas('transportationRequestForm', function (Builder $query) use ($personnelId) {
                    $query->where('form_creator_id', $personnelId)
                        ->whereIn('status', ['For Evaluation', 'Completed']);
                })
                ->orderByRaw("CASE WHEN status = 'Pending' THEN 0 ELSE 1 END")
                ->orderBy('copy_number')
                ->orderBy('id')
                ->first();
        }

        if (!$selectedEvaluationRecord) {
            $selectedEvaluationRecord = $pendingEvaluations->first();
        }

        $selectedEvaluation = $selectedEvaluationRecord?->transportationRequestForm;

        $selectedEvaluationPayload = is_array($selectedEvaluationRecord?->evaluation_payload)
            ? $selectedEvaluationRecord->evaluation_payload
            : [];

        return view('drivers_evaluation.evaluation_performance', [
            'pendingEvaluations' => $pendingEvaluations,
            'selectedEvaluation' => $selectedEvaluation,
            'selectedEvaluationRecord' => $selectedEvaluationRecord,
            'selectedEvaluationPayload' => $selectedEvaluationPayload,
            'pendingEvaluationCount' => (clone $baseQuery)->count(),
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $validationRules = [
            'evaluation_id' => ['required', 'integer', 'exists:driver_performance_evaluations,id'],
            'evaluation_date' => ['required', 'date_format:Y-m-d'],
            'comments_improvement' => ['nullable', 'string', 'max:2000'],
            'comments_praise' => ['nullable', 'string', 'max:2000'],
        ];

        for ($index = 1; $index <= 8; $index += 1) {
            $validationRules['r' . $index] = ['required', 'integer', 'between:1,5'];
            $validationRules['remark_r' . $index] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($validationRules);

        $personnelId = (string) (Auth::user()?->personnel_id ?? '');

        $evaluation = DriverPerformanceEvaluation::query()
            ->with([
                'transportationRequestForm:id,form_id,form_creator_id,destination,status,driver_name',
            ])
            ->whereKey((int) $validated['evaluation_id'])
            ->whereHas('transportationRequestForm', function (Builder $query) use ($personnelId) {
                $query->where('form_creator_id', $personnelId)
                    ->whereIn('status', ['For Evaluation', 'Completed']);
            })
            ->first();

        if (!$evaluation) {
            throw ValidationException::withMessages([
                'evaluation_id' => 'The selected driver evaluation is invalid or unavailable.',
            ]);
        }

        $transportationRequest = $evaluation->transportationRequestForm;
        if (!$transportationRequest) {
            throw ValidationException::withMessages([
                'evaluation_id' => 'The selected driver evaluation does not have a valid transportation request.',
            ]);
        }

        if ((string) ($transportationRequest->status ?? '') !== 'For Evaluation') {
            throw ValidationException::withMessages([
                'evaluation_id' => 'This trip is no longer pending evaluation.',
            ]);
        }

        if (strtolower(trim((string) ($evaluation->status ?? ''))) !== 'pending') {
            throw ValidationException::withMessages([
                'evaluation_id' => 'This driver evaluation has already been submitted.',
            ]);
        }

        $criteriaPayload = $this->buildCriteriaPayload($validated);
        $scores = array_values($criteriaPayload['scores']);
        $overallRating = round(array_sum($scores) / max(count($scores), 1), 2);

        $improvementComments = trim((string) ($validated['comments_improvement'] ?? ''));
        $praiseComments = trim((string) ($validated['comments_praise'] ?? ''));

        $criteriaTailScores = array_slice($scores, 2);
        $complianceScore = (int) round(array_sum($criteriaTailScores) / max(count($criteriaTailScores), 1));

        $evaluationDateTime = Carbon::parse((string) $validated['evaluation_date'])
            ->setTimeFromTimeString(now()->format('H:i:s'));

        $driverNotifiedCount = 0;

        DB::transaction(function () use (
            $transportationRequest,
            $evaluation,
            $criteriaPayload,
            $overallRating,
            $improvementComments,
            $praiseComments,
            $complianceScore,
            $evaluationDateTime,
            &$driverNotifiedCount,
            $validated
        ) {
            $evaluation->fill([
                'status' => 'Submitted',
                'overall_rating' => $overallRating,
                'timeliness_score' => (int) ($criteriaPayload['scores']['r1'] ?? 0),
                'safety_score' => (int) ($criteriaPayload['scores']['r2'] ?? 0),
                'compliance_score' => $complianceScore,
                'evaluator_name' => trim((string) (Auth::user()?->name ?? $transportationRequest->requestor_name ?: 'N/A')),
                'comments' => $this->buildCommentSummary($criteriaPayload, $improvementComments, $praiseComments),
                'evaluation_payload' => [
                    'evaluation_date' => (string) $validated['evaluation_date'],
                    'criteria' => $criteriaPayload,
                    'improvement_comments' => $improvementComments,
                    'praise_comments' => $praiseComments,
                    'overall_rating' => $overallRating,
                    'overall_label' => $this->resolveRatingLabel($overallRating),
                ],
                'evaluated_at' => $evaluationDateTime,
            ]);
            $evaluation->save();

            $hasPendingEvaluations = DriverPerformanceEvaluation::query()
                ->where('transportation_request_form_id', (int) $transportationRequest->id)
                ->where('status', 'Pending')
                ->exists();

            if (!$hasPendingEvaluations) {
                $transportationRequest->update([
                    'status' => 'Completed',
                ]);
            }

            $driverNotifiedCount = $this->notifyAssignedDrivers($transportationRequest, $evaluation);
        });

        $flashMessage = 'Driver performance evaluation submitted successfully.';
        if ($driverNotifiedCount > 0) {
            $flashMessage .= ' ' . $driverNotifiedCount . ' assigned driver' . ($driverNotifiedCount === 1 ? '' : 's') . ' notified.';
        } else {
            $flashMessage .= ' No matching driver account was found to notify.';
        }

        return redirect()
            ->route('evaluation-performance', ['evaluation_id' => $evaluation->id])
            ->with('evaluation_submit_success', $flashMessage)
            ->with('auto_print_evaluation', true);
    }

    private function buildCriteriaPayload(array $validated): array
    {
        $descriptors = [
            'r1' => 'Punctuality',
            'r2' => 'Safe Driving',
            'r3' => 'Courtesy',
            'r4' => 'Personal Attitude',
            'r5' => 'Knowledge of direction to destination',
            'r6' => 'Personal Hygiene',
            'r7' => 'Trouble shooting',
            'r8' => 'Vehicle Cleanliness',
        ];

        $scores = [];
        $remarks = [];

        foreach ($descriptors as $key => $label) {
            $scores[$key] = (int) ($validated[$key] ?? 0);
            $remarks[$key] = trim((string) ($validated['remark_' . $key] ?? ''));
        }

        return [
            'descriptors' => $descriptors,
            'scores' => $scores,
            'remarks' => $remarks,
        ];
    }

    private function buildCommentSummary(array $criteriaPayload, string $improvementComments, string $praiseComments): string
    {
        $lines = ['Criteria Ratings:'];

        $descriptors = is_array($criteriaPayload['descriptors'] ?? null)
            ? $criteriaPayload['descriptors']
            : [];
        $scores = is_array($criteriaPayload['scores'] ?? null)
            ? $criteriaPayload['scores']
            : [];
        $remarks = is_array($criteriaPayload['remarks'] ?? null)
            ? $criteriaPayload['remarks']
            : [];

        foreach ($descriptors as $key => $label) {
            $score = (int) ($scores[$key] ?? 0);
            $remark = trim((string) ($remarks[$key] ?? ''));

            $line = $label . ': ' . $score . '/5';
            if ($remark !== '') {
                $line .= ' - ' . $remark;
            }

            $lines[] = $line;
        }

        if ($improvementComments !== '') {
            $lines[] = '';
            $lines[] = 'For Improvement: ' . $improvementComments;
        }

        if ($praiseComments !== '') {
            $lines[] = '';
            $lines[] = 'For Praise: ' . $praiseComments;
        }

        return implode(PHP_EOL, $lines);
    }

    private function resolveRatingLabel(float $score): string
    {
        if ($score <= 0) {
            return 'Not Rated';
        }

        if ($score < 1.5) {
            return 'Poor';
        }

        if ($score < 2.5) {
            return 'Fair';
        }

        if ($score < 3.5) {
            return 'Good';
        }

        if ($score < 4.5) {
            return 'Very Good';
        }

        return 'Excellent';
    }

    private function notifyAssignedDrivers(TransportationRequestFormModel $transportationRequest, DriverPerformanceEvaluation $evaluation): int
    {
        $driverNames = $this->parseDriverNames((string) ($evaluation->driver_name ?? ''));
        if (empty($driverNames)) {
            $driverNames = $this->parseDriverNames((string) ($transportationRequest->driver_name ?? ''));
        }

        if (empty($driverNames)) {
            return 0;
        }

        $normalizedDriverNames = collect($driverNames)
            ->map(function (string $name) {
                return strtolower(trim($name));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($normalizedDriverNames)) {
            return 0;
        }

        $matchedDriverUsers = User::query()
            ->select(['id', 'name'])
            ->get()
            ->filter(function (User $user) use ($normalizedDriverNames) {
                $normalizedUserName = strtolower(trim((string) ($user->name ?? '')));

                return in_array($normalizedUserName, $normalizedDriverNames, true);
            })
            ->values();

        $notificationCount = 0;

        foreach ($matchedDriverUsers as $driverUser) {
            $driverUser->notify(new DriverPerformanceEvaluationSubmittedNotification($transportationRequest, $evaluation));
            $notificationCount += 1;
        }

        return $notificationCount;
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
