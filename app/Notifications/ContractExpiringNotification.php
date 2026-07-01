<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(public Contract $contract) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'contract_expiring',
            'contract_id' => $this->contract->id,
            'user_id' => $this->contract->user_id,
            'end_date' => $this->contract->end_date?->toDateString(),
            'message' => __('notifications.contract_expiring'),
        ];
    }
}
