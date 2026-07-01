<?php

namespace App\Models;

use App\Enums\CorrespondencePriority;
use App\Enums\CorrespondenceStatus;
use App\Enums\CorrespondenceType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Correspondence extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'number',
        'number_seq',
        'type',
        'subject',
        'body',
        'sender_info',
        'receiver_info',
        'priority',
        'status',
        'parent_id',
        'attachments',
        'created_by_user_id',
        'signed_by_user_id',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CorrespondenceType::class,
            'priority' => CorrespondencePriority::class,
            'status' => CorrespondenceStatus::class,
            'attachments' => 'array',
            'signed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<Correspondence, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Correspondence::class, 'parent_id');
    }

    /** @return HasMany<CorrespondenceRouting, $this> */
    public function routings(): HasMany
    {
        return $this->hasMany(CorrespondenceRouting::class);
    }
}
