<?php

namespace App\Models;

use App\Enums\RequisitionPriority;
use App\Enums\RequisitionStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisition extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'title',
        'department',
        'positions_count',
        'description',
        'requirements',
        'priority',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => RequisitionPriority::class,
            'status' => RequisitionStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return HasMany<Candidate, $this> */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
}
