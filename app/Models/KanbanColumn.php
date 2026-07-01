<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\KanbanColumnFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    /** @use HasFactory<KanbanColumnFactory> */
    use BelongsToTenant, HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'slug',
        'color',
        'order_index',
        'wip_limit',
        'is_done_column',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'order_index' => 'decimal:4',
            'wip_limit' => 'integer',
            'is_done_column' => 'boolean',
            'created_at' => 'datetime',
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
