<?php

namespace App\Models;

use App\Enums\ChatMemberRole;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatChannelMember extends Model
{
    use BelongsToTenant, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'user_id',
        'role',
        'joined_at',
        'muted_until',
        'last_read_message_id',
    ];

    protected function casts(): array
    {
        return [
            'role' => ChatMemberRole::class,
            'joined_at' => 'datetime',
            'muted_until' => 'datetime',
        ];
    }

    /** @return BelongsTo<ChatChannel, $this> */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'channel_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ChatMessage, $this> */
    public function lastReadMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'last_read_message_id');
    }
}
