<?php

namespace App\Models;

use App\Enums\ChatChannelType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatChannel extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'description',
        'avatar_url',
        'is_archived',
        'created_by_user_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ChatChannelType::class,
            'is_archived' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return HasMany<ChatChannelMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(ChatChannelMember::class, 'channel_id');
    }

    /** @return HasMany<ChatMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'channel_id');
    }
}
