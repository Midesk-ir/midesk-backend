<?php

namespace App\Models;

use App\Enums\ChatMessageType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use BelongsToTenant, HasUuids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'sender_user_id',
        'body',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'reply_to_id',
        'edited_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChatMessageType::class,
            'edited_at' => 'datetime',
            'deleted_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<ChatChannel, $this> */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'channel_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /** @return BelongsTo<ChatMessage, $this> */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    /** @return HasMany<ChatReaction, $this> */
    public function reactions(): HasMany
    {
        return $this->hasMany(ChatReaction::class, 'message_id');
    }
}
