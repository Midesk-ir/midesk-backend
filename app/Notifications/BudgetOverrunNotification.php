<?php

namespace App\Notifications;

use App\Models\Budget;
use App\Notifications\Channels\TenantDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BudgetOverrunNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Budget $budget,
        private readonly float $utilization,
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
            'budget_id' => $this->budget->id,
            'utilization_percent' => $this->utilization,
            'message' => __('notifications.budget_overrun'),
        ];
    }
}
