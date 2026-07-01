<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Notifications\Channels\TenantDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OverdueInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Invoice $invoice,
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
            'invoice_id' => $this->invoice->id,
            'message' => __('notifications.invoice_overdue'),
        ];
    }
}
