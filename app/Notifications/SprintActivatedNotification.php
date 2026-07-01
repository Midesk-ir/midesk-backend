<?php

namespace App\Notifications;

use App\Models\Sprint;
use App\Notifications\Channels\TenantDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SprintActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Sprint $sprint,
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
            'message' => __('notifications.sprint_activated', ['name' => $this->sprint->name]),
        ];
    }
}
