<?php

namespace App\Models;

use App\Enums\SprintStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\SprintFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    /** @use HasFactory<SprintFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'goal',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => SprintStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
