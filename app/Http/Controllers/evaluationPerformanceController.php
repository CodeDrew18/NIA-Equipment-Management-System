<?php

namespace App\Http\Controllers;

use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use App\Services\FcmPushService;
use App\Models\User;
use App\Support\TripLifecycleManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\TemplateProcessor;

class evaluationPerformanceController extends Controller
{
    private const DPE_ATTACHMENT_KEY_PREFIX = 'driver_performance_evaluation_file';

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

    public function submit(Request $request, FcmPushService $fcmPushService): RedirectResponse
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
        $evaluationAttachmentUrl = null;

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
            &$evaluationAttachmentUrl,
            $validated,
            $fcmPushService
        ) {
            $attachmentPayload = $this->generateEvaluationDocumentAttachment(
                $evaluation,
                $validated,
                $overallRating
            );

            $evaluation->fill([
                'status' => 'Submitted',
                'overall_rating' => $overallRating,
                'timeliness_score' => (int) ($criteriaPayload['scores']['r1'] ?? 0),
                'safety_score' => (int) ($criteriaPayload['scores']['r2'] ?? 0),
                'compliance_score' => $complianceScore,
                'evaluator_name' => trim((string) (Auth::user()?->name ?? $transportationRequest->requestor_name ?: 'N/A')),
                'attachment' => $attachmentPayload,
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

            $savedAttachments = $transportationRequest->upsertAttachment($attachmentPayload);
            $evaluationAttachmentUrl = $this->resolveEvaluationAttachmentUrl(
                $transportationRequest,
                $savedAttachments,
                $attachmentPayload
            );

            $hasPendingEvaluations = DriverPerformanceEvaluation::query()
                ->where('transportation_request_form_id', (int) $transportationRequest->id)
                ->where('status', 'Pending')
                ->exists();

            if (!$hasPendingEvaluations) {
                $transportationRequest->update([
                    'status' => 'Completed',
                ]);
            }

            $driverNotifiedCount = $this->notifyAssignedDrivers($transportationRequest, $evaluation, $fcmPushService);
        });

        $flashMessage = 'Driver performance evaluation submitted successfully.';
        if ($driverNotifiedCount > 0) {
            $flashMessage .= ' ' . $driverNotifiedCount . ' assigned driver' . ($driverNotifiedCount === 1 ? '' : 's') . ' notified.';
        } else {
            $flashMessage .= ' No push sent (missing FCM token or unmatched driver account name).';
        }

        return redirect()
            ->route('evaluation-performance', ['evaluation_id' => $evaluation->id])
            ->with('evaluation_submit_success', $flashMessage)
            ->with('auto_open_evaluation_docx', $evaluationAttachmentUrl !== null)
            ->with('evaluation_attachment_url', $evaluationAttachmentUrl);
    }

    private function generateEvaluationDocumentAttachment(
        DriverPerformanceEvaluation $evaluation,
        array $validated,
        float $overallRating
    ): array {
        $templatePath = $this->resolveEvaluationTemplatePath();

        $transportationRequest = TransportationRequestFormModel::query()
            ->findOrFail((int) $evaluation->transportation_request_form_id);

        $outputDirectory = Storage::disk('public')->path('generated_forms');
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $fileName = 'driver_performance_evaluation_' . ($transportationRequest->form_id ?: 'request')
            . '_' . now()->format('Ymd_His_u') . '_' . Str::lower(Str::random(6)) . '.docx';
        $safeFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName)
            ?: ('driver_performance_evaluation_' . now()->format('Ymd_His_u') . '.docx');
        $relativePath = 'generated_forms/' . $safeFileName;
        $outputPath = Storage::disk('public')->path($relativePath);

        $from = $transportationRequest->date_time_from ? Carbon::parse($transportationRequest->date_time_from) : null;
        $to = $transportationRequest->date_time_to ? Carbon::parse($transportationRequest->date_time_to) : null;

        $duration = 'N/A';
        if ($from && $to) {
            $duration = $from->format('M d, Y h:i A') . ' to ' . $to->format('M d, Y h:i A');
        }

        $driverName = (string) ($evaluation->driver_name ?: $transportationRequest->driver_name ?: 'N/A');
        $evaluationDate = Carbon::parse((string) $validated['evaluation_date'])->format('M d, Y');
        $vehicle = (string) ($transportationRequest->vehicle_type ?: 'N/A');
        $plateNo = (string) ($transportationRequest->vehicle_id ?: 'N/A');
        $destination = (string) ($transportationRequest->destination ?: 'N/A');
        $purposeTravel = (string) ($transportationRequest->purpose ?: 'N/A');
        $overallRatingText = number_format($overallRating, 2);
        $personnelName = trim((string) ($transportationRequest->division_personnel[0]['name'] ?? 'N/A')) ?: 'N/A';

        $remarksCombined = trim(implode(' | ', array_filter([
            trim((string) ($validated['comments_improvement'] ?? '')),
            trim((string) ($validated['comments_praise'] ?? '')),
        ])));
        if ($remarksCombined === '') {
            $remarksCombined = 'N/A';
        }

        // dd([
        //     'personnel_name' => $personnelName,
        //     'driver_name' => $driverName,
        //     'date' => $evaluationDate,
        //     'vehicle' => $vehicle,
        //     'plate_no' => $plateNo,
        //     'destination' => $destination,
        //     'purpose_travel' => $purposeTravel,
        //     'duration_travel' => $duration,
        //     'overall_rating' => $overallRatingText,
        //     'rating' => $overallRatingText,
        //     'remarks' => $remarksCombined,
        // ]);

        // dd([
        //     'driver_name' => $driverName,
        //     'date' => $evaluationDate,
        //     'vehicle' => $vehicle,
        //     'plate_no' => $plateNo,
        //     'destination' => $destination,
        //     'purpose_travel' => $purposeTravel,
        //     'duration_travel' => $duration,
        //     'overall_rating' => $overallRatingText,
        //     'personnel_name' => $personnelName,

        //     // ratings
        //     'r1' => $validated['r1'] ?? null,
        //     'r2' => $validated['r2'] ?? null,
        //     'r3' => $validated['r3'] ?? null,
        //     'r4' => $validated['r4'] ?? null,
        //     'r5' => $validated['r5'] ?? null,
        //     'r6' => $validated['r6'] ?? null,
        //     'r7' => $validated['r7'] ?? null,
        //     'r8' => $validated['r8'] ?? null,

        //     // remarks (IMPORTANT)
        //     'remarks_array' => [
        //         'r1' => $validated['remark_r1'] ?? null,
        //         'r2' => $validated['remark_r2'] ?? null,
        //         'r3' => $validated['remark_r3'] ?? null,
        //         'r4' => $validated['remark_r4'] ?? null,
        //         'r5' => $validated['remark_r5'] ?? null,
        //         'r6' => $validated['remark_r6'] ?? null,
        //         'r7' => $validated['remark_r7'] ?? null,
        //         'r8' => $validated['remark_r8'] ?? null,
        //     ],
        //     'remark_r1' => $validated['remark_r1'] ?? null,
        //     'remark_r2' => $validated['remark_r2'] ?? null,

        //     // comments
        //     'improvement_comments' => $validated['comments_improvement'] ?? null,
        //     'praise_comments' => $validated['comments_praise'] ?? null,

        //     'overall_label' => $this->resolveRatingLabel($overallRating),
        // ]);

        $templateProcessor = new TemplateProcessor($templatePath);

        // Primary placeholders
        $templateProcessor->setValue('driver_name', $driverName);
        $templateProcessor->setValue('date', $evaluationDate);
        $templateProcessor->setValue('vehicle', $vehicle);
        $templateProcessor->setValue('plate_no', $plateNo);
        $templateProcessor->setValue('destination', $destination);
        $templateProcessor->setValue('purpose_travel', $purposeTravel);
        $templateProcessor->setValue('duration_travel', $duration);
        $templateProcessor->setValue('overall_rating', $overallRatingText);
        $templateProcessor->setValue('personnel_name', $personnelName);

        // Aliases for template compatibility (example style)
        $templateProcessor->setValue('rating', $this->normalizeDocxText($overallRatingText));
        $templateProcessor->setValue('remarks', $this->normalizeDocxText($remarksCombined));

        // Criteria ratings
        $templateProcessor->setValue('r1', $this->normalizeDocxText((string) ((int) ($validated['r1'] ?? 0))));
        $templateProcessor->setValue('r2', $this->normalizeDocxText((string) ((int) ($validated['r2'] ?? 0))));
        $templateProcessor->setValue('r3', $this->normalizeDocxText((string) ((int) ($validated['r3'] ?? 0))));
        $templateProcessor->setValue('r4', $this->normalizeDocxText((string) ((int) ($validated['r4'] ?? 0))));
        $templateProcessor->setValue('r5', $this->normalizeDocxText((string) ((int) ($validated['r5'] ?? 0))));
        $templateProcessor->setValue('r6', $this->normalizeDocxText((string) ((int) ($validated['r6'] ?? 0))));
        $templateProcessor->setValue('r7', $this->normalizeDocxText((string) ((int) ($validated['r7'] ?? 0))));
        $templateProcessor->setValue('r8', $this->normalizeDocxText((string) ((int) ($validated['r8'] ?? 0))));

        // Criteria remarks
        $templateProcessor->setValue('remark_r1', $this->normalizeDocxText((string) ($validated['remark_r1'] ?? '')));
        $templateProcessor->setValue('remark_r2', $this->normalizeDocxText((string) ($validated['remark_r2'] ?? '')));
        $templateProcessor->setValue('remark_r3', $this->normalizeDocxText((string) ($validated['remark_r3'] ?? '')));
        $templateProcessor->setValue('remark_r4', $this->normalizeDocxText((string) ($validated['remark_r4'] ?? '')));
        $templateProcessor->setValue('remark_r5', $this->normalizeDocxText((string) ($validated['remark_r5'] ?? '')));
        $templateProcessor->setValue('remark_r6', $this->normalizeDocxText((string) ($validated['remark_r6'] ?? '')));
        $templateProcessor->setValue('remark_r7', $this->normalizeDocxText((string) ($validated['remark_r7'] ?? '')));
        $templateProcessor->setValue('remark_r8', $this->normalizeDocxText((string) ($validated['remark_r8'] ?? '')));
        // $templateProcessor->setValue('remark_r1', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r1'] ?? '')));
        // $templateProcessor->setValue('remark_r2', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r2'] ?? '')));
        // $templateProcessor->setValue('remark_r3', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r3'] ?? '')));
        // $templateProcessor->setValue('remark_r4', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r4'] ?? '')));
        // $templateProcessor->setValue('remark_r5', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r5'] ?? '')));
        // $templateProcessor->setValue('remark_r6', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r6'] ?? '')));
        // $templateProcessor->setValue('remark_r7', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r7'] ?? '')));
        // $templateProcessor->setValue('remark_r8', $this->normalizeDocxText((string) ($validated['criteria']['remarks']['r8'] ?? '')));

        $templateProcessor->setValue('improvement_comments', $this->formatCommentWithUnderlineAndWrap($validated['comments_improvement'] ?? '', 58));
        $templateProcessor->setValue('praise_comments', $this->formatCommentWithUnderlineAndWrap($validated['comments_praise'] ?? '', 58));

        $templateProcessor->setValue('overall_label', $this->normalizeDocxText($this->resolveRatingLabel($overallRating)));

        $templateProcessor->saveAs($outputPath);

        return [
            'file_name' => $safeFileName,
            'file_path' => $relativePath,
            'process' => 'driver_performance_evaluation',
            'process_key' => self::DPE_ATTACHMENT_KEY_PREFIX . '_' . ($evaluation->copy_key ?: $evaluation->id),
            'source' => 'evaluation_performance_submit',
            'copy_key' => (string) ($evaluation->copy_key ?? ''),
        ];
    }

    private function resolveEvaluationAttachmentUrl(
        TransportationRequestFormModel $transportationRequest,
        array $attachments,
        array $attachmentPayload
    ): ?string {
        $processKey = trim((string) ($attachmentPayload['process_key'] ?? ''));
        $filePath = trim((string) ($attachmentPayload['file_path'] ?? ''));

        $index = collect($attachments)->search(function ($attachment) use ($processKey, $filePath) {
            if (!is_array($attachment)) {
                return false;
            }

            $attachmentProcessKey = trim((string) ($attachment['process_key'] ?? ''));
            $attachmentFilePath = trim((string) ($attachment['file_path'] ?? ''));

            if ($processKey !== '' && $attachmentProcessKey === $processKey) {
                return true;
            }

            return $filePath !== '' && $attachmentFilePath === $filePath;
        });

        if ($index === false) {
            return null;
        }

        return route('request-form.attachment.view', [
            'transportationRequest' => $transportationRequest->id,
            'index' => (int) $index,
        ]);
    }

    private function resolveEvaluationTemplatePath(): string
    {
        $candidates = [
            storage_path('app/public/forms/form15_rev_00.docx'),
            storage_path('app/public/forms/form_15_rev_00.docx'),
        ];

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        abort(500, 'Evaluation template file not found: form15_rev_00.docx');
    }

    private function normalizeDocxText(string $value): string
    {
        $normalized = str_replace(["\r\n", "\r", "\n"], ' ', trim($value));

        return preg_replace('/\s+/', ' ', $normalized) ?: '';
    }

    private function formatCommentWithUnderlineAndWrap(string $value, int $lineLimit = 58): string
    {
        if (trim($value) === '') {
            return '';
        }

        $normalized = $this->normalizeDocxText($value);

        // Split text into lines that respect word boundaries
        $words = explode(' ', $normalized);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            // Check if adding this word exceeds the limit
            if (strlen($currentLine) + strlen($word) + 1 > $lineLimit && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = ($currentLine === '') ? $word : $currentLine . ' ' . $word;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        // Join lines with newline and tab for indentation
        $formattedText = implode("\n\t", $lines);

        // Create XML with underline formatting for PHPWord/DOCX
        // The underline formatting is embedded in the text itself as a marker
        // The DOCX template fields need to support this formatting
        return $formattedText;
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

    private function notifyAssignedDrivers(
        TransportationRequestFormModel $transportationRequest,
        DriverPerformanceEvaluation $evaluation,
        FcmPushService $fcmPushService
    ): int {
        $driverNames = $this->parseDriverNames((string) ($evaluation->driver_name ?? ''));
        if (empty($driverNames)) {
            $driverNames = $this->parseDriverNames((string) ($transportationRequest->driver_name ?? ''));
        }

        if (empty($driverNames)) {
            return 0;
        }

        $normalizedDriverNames = collect($driverNames)
            ->map(function (string $name) {
                return $this->normalizePersonName($name);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($normalizedDriverNames)) {
            return 0;
        }

        $matchedDriverUsers = User::query()
            ->select(['id', 'name', 'fcm_token'])
            ->get()
            ->filter(function (User $user) use ($normalizedDriverNames) {
                $normalizedUserName = $this->normalizePersonName((string) ($user->name ?? ''));

                return in_array($normalizedUserName, $normalizedDriverNames, true);
            })
            ->values();

        if ($matchedDriverUsers->isEmpty()) {
            Log::warning('Evaluation push skipped because no matching user account names were found.', [
                'transportation_request_form_id' => $transportationRequest->id,
                'driver_names' => $driverNames,
            ]);

            return 0;
        }

        $driverUsers = $matchedDriverUsers
            ->filter(function (User $user) {
                return trim((string) ($user->fcm_token ?? '')) !== '';
            })
            ->values();

        if ($driverUsers->isEmpty()) {
            Log::warning('Evaluation push skipped because matched drivers have no registered FCM tokens.', [
                'transportation_request_form_id' => $transportationRequest->id,
                'matched_driver_user_ids' => $matchedDriverUsers->pluck('id')->values()->all(),
            ]);

            return 0;
        }

        $notificationCount = 0;
        $formId = (string) ($transportationRequest->form_id ?? 'N/A');
        $destination = $this->normalizeInlineText((string) ($transportationRequest->destination ?? 'N/A'));
        $overallRating = $evaluation->overall_rating !== null
            ? number_format((float) $evaluation->overall_rating, 2)
            : null;
        $evaluatedAt = optional($evaluation->evaluated_at)->format('M d, Y h:i A')
            ?: now()->format('M d, Y h:i A');

        $notificationTitle = 'Driver Evaluation Submitted';
        $notificationBody = 'Your trip performance evaluation for ' . $formId . ' was submitted.';
        if ($overallRating !== null) {
            $notificationBody .= "\nOverall rating: " . $overallRating . '/5.00';
        }
        $notificationBody .= "\nDestination: " . ($destination !== '' ? $destination : 'N/A');
        $notificationBody .= "\nEvaluated at: " . $evaluatedAt;

        foreach ($driverUsers as $driverUser) {
            try {
                $isSent = $fcmPushService->sendToToken(
                    (string) $driverUser->fcm_token,
                    $notificationTitle,
                    $notificationBody,
                    [
                        'type' => 'driver_performance_evaluation',
                        'transportation_request_form_id' => (string) $transportationRequest->id,
                        'evaluation_id' => (string) $evaluation->id,
                        'form_id' => $formId,
                        'destination' => (string) ($destination !== '' ? $destination : 'N/A'),
                        'driver_name' => (string) ($evaluation->driver_name ?? ''),
                        'evaluator_name' => (string) ($evaluation->evaluator_name ?? 'N/A'),
                        'overall_rating' => (string) ($overallRating ?? ''),
                        'evaluated_at' => $evaluatedAt,
                    ]
                );

                if ($isSent) {
                    $notificationCount += 1;
                }
            } catch (\Throwable $exception) {
                Log::warning('Failed sending evaluation push notification.', [
                    'transportation_request_form_id' => $transportationRequest->id,
                    'evaluation_id' => $evaluation->id,
                    'driver_user_id' => $driverUser->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $notificationCount;
    }

    private function normalizePersonName(string $name): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''));
    }

    private function normalizeInlineText(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
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
