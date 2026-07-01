<?php

use App\Models\ChatChannelMember;
use App\Models\Membership;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{channelId}', function ($user, string $channelId) {
    return ChatChannelMember::query()
        ->where('channel_id', $channelId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('tenant.{tenantId}', function ($user, string $tenantId) {
    return Membership::query()
        ->where('tenant_id', $tenantId)
        ->where('user_id', $user->id)
        ->exists();
});
