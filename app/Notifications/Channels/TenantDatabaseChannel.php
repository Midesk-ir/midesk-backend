<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;

class TenantDatabaseChannel extends DatabaseChannel
{
    /** @param  mixed  $notifiable */
    public function buildPayload($notifiable, Notification $notification): array
    {
        $payload = parent::buildPayload($notifiable, $notification);

        $payload['tenant_id'] = app('tenant')?->id;

        return $payload;
    }
}
