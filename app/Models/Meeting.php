<?php

namespace App\Models;

use App\Enums\MeetingAttendeeStatus;
use App\Enums\MeetingProvider;
use App\Enums\MeetingStatus;
use App\Enums\MeetingType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'organizer_user_id',
        'start_at',
        'end_at',
        'type',
        'provider',
        'room_url',
        'room_id',
        'status',
        'recording_url',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'type' => MeetingType::class,
            'provider' => MeetingProvider::class,
            'status' => MeetingStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_user_id');
    }

    /** @return HasMany<MeetingAttendee, $this> */
    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }
}
