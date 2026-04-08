<?php

namespace App\Notifications;

use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DriverPerformanceEvaluationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly TransportationRequestFormModel $transportationRequest,
        private readonly DriverPerformanceEvaluation $evaluation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $formId = (string) ($this->transportationRequest->form_id ?? 'N/A');
        $destination = (string) ($this->transportationRequest->destination ?? 'N/A');
        $overallRating = $this->evaluation->overall_rating !== null
            ? number_format((float) $this->evaluation->overall_rating, 2)
            : null;

        $message = 'Your trip performance evaluation for ' . $formId . ' was submitted.';
        if ($overallRating !== null) {
            $message .= ' Overall rating: ' . $overallRating . '/5.00.';
        }

        return [
            'type' => 'driver_performance_evaluation',
            'transportation_request_form_id' => (int) $this->transportationRequest->id,
            'form_id' => $formId,
            'destination' => $destination,
            'driver_name' => (string) ($this->evaluation->driver_name ?? ''),
            'evaluator_name' => (string) ($this->evaluation->evaluator_name ?? 'N/A'),
            'overall_rating' => $overallRating,
            'evaluated_at' => optional($this->evaluation->evaluated_at)->toDateTimeString(),
            'message' => $message,
            'action_url' => route('monthly-official-travel-report'),
        ];
    }
}
