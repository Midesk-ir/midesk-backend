<?php

namespace App\Notifications;

use App\Models\Membership;
use App\Notifications\Channels\TenantDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberInvitedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Membership $membership,
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
            'membership_id' => $this->membership->id,
            'role' => $this->membership->role?->value,
            'message' => __('notifications.member_invited'),
        ];
    }
}
