<?php

namespace App\Notifications;

use App\Models\PerformanceReview;
use App\Notifications\Channels\TenantDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewCycleReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ?PerformanceReview $review = null,
        private readonly ?string $period = null,
    ) {}

    /** @return list<class-string|string> */
    public function via(object $notifiable): array
    {
        return [TenantDatabaseChannel::class];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'review_id' => $this->review?->id,
            'period' => $this->period ?? $this->review?->period,
            'message' => __('notifications.review_cycle_reminder'),
        ];
    }
}
