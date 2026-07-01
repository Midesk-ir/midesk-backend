<?php

namespace App\Notifications;

use App\Models\Sprint;
use App\Notifications\Channels\TenantDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SprintDeadlineApproachingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Sprint $sprint,
        private readonly int $daysRemaining,
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
            'sprint_id' => $this->sprint->id,
            'project_id' => $this->sprint->project_id,
            'days_remaining' => $this->daysRemaining,
            'message' => __('notifications.sprint_deadline_approaching', [
                'name' => $this->sprint->name,
                'days' => $this->daysRemaining,
            ]),
        ];
    }
}
