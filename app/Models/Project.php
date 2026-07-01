<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'key',
        'description',
        'status',
        'owner_user_id',
        'start_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->using(ProjectMember::class)
            ->withPivot(['id', 'role', 'tenant_id'])
            ->withTimestamps();
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @return HasMany<Sprint, $this> */
    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    /** @return HasMany<KanbanColumn, $this> */
    public function kanbanColumns(): HasMany
    {
        return $this->hasMany(KanbanColumn::class)->orderBy('order_index');
    }
}
