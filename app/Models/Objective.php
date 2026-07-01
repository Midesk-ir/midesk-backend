<?php

namespace App\Models;

use App\Enums\ObjectiveStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ObjectiveFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Objective extends Model
{
    /** @use HasFactory<ObjectiveFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'description',
        'period',
        'status',
        'progress_percent',
        'parent_objective_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ObjectiveStatus::class,
            'progress_percent' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Objective, $this> */
    public function parentObjective(): BelongsTo
    {
        return $this->belongsTo(Objective::class, 'parent_objective_id');
    }

    /** @return HasMany<KeyResult, $this> */
    public function keyResults(): HasMany
    {
        return $this->hasMany(KeyResult::class);
    }
}
