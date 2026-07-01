<?php

namespace App\Models;

use App\Enums\CandidateSource;
use App\Enums\CandidateStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'tenant_id',
        'requisition_id',
        'full_name',
        'email',
        'phone',
        'resume_path',
        'source',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'source' => CandidateSource::class,
            'status' => CandidateStatus::class,
        ];
    }

    /** @return BelongsTo<Requisition, $this> */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    /** @return HasMany<Interview, $this> */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
