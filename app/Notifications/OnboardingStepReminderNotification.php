<?php

namespace App\Notifications;

use App\Models\OnboardingProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OnboardingStepReminderNotification extends Notification
{
    use Queueable;

    /** @param array<string, mixed> $step */
    public function __construct(
        public OnboardingProgress $progress,
        public array $step,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'onboarding_step_reminder',
            'progress_id' => $this->progress->id,
            'user_id' => $this->progress->user_id,
            'step_title' => $this->step['title'] ?? '',
            'message' => __('notifications.onboarding_step_reminder'),
        ];
    }
}
