<?php

namespace App\Notifications;

use App\Models\Sprint;
use App\Models\Task;
use App\Notifications\Channels\TenantDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskAddedToSprintNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
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
            'task_id' => $this->task->id,
            'sprint_id' => $this->sprint->id,
            'message' => __('notifications.task_added_to_sprint', [
                'title' => $this->task->title,
                'sprint' => $this->sprint->name,
            ]),
        ];
    }
}
