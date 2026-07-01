<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\KanbanColumn;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class KanbanColumnService
{
    /** @var list<array{name: string, slug: string, order_index: float, is_done_column: bool}> */
    public const DEFAULT_COLUMNS = [
        ['name' => 'Backlog', 'slug' => 'backlog', 'order_index' => 1024, 'is_done_column' => false],
        ['name' => 'To Do', 'slug' => 'todo', 'order_index' => 2048, 'is_done_column' => false],
        ['name' => 'In Progress', 'slug' => 'in_progress', 'order_index' => 3072, 'is_done_column' => false],
        ['name' => 'Review', 'slug' => 'review', 'order_index' => 4096, 'is_done_column' => false],
        ['name' => 'Done', 'slug' => 'done', 'order_index' => 5120, 'is_done_column' => true],
    ];

    /** @return Collection<int, KanbanColumn> */
    public function createDefaultColumns(Project $project): Collection
    {
        $columns = collect();

        foreach (self::DEFAULT_COLUMNS as $definition) {
            $columns->push(KanbanColumn::create([
                'tenant_id' => $project->tenant_id,
                'project_id' => $project->id,
                ...$definition,
            ]));
        }

        return $columns;
    }

    public function createColumn(Project $project, array $data): KanbanColumn
    {
        $slug = $data['slug'] ?? Str::slug($data['name'], '_');

        return KanbanColumn::create([
            'tenant_id' => $project->tenant_id,
            'project_id' => $project->id,
            'name' => $data['name'],
            'slug' => $slug,
            'color' => $data['color'] ?? null,
            'order_index' => $data['order_index'] ?? 8192,
            'wip_limit' => $data['wip_limit'] ?? null,
            'is_done_column' => $data['is_done_column'] ?? false,
        ]);
    }

    public function updateColumn(KanbanColumn $column, array $data): KanbanColumn
    {
        if (array_key_exists('is_done_column', $data) && ! $data['is_done_column'] && $column->is_done_column) {
            $otherDone = KanbanColumn::query()
                ->where('project_id', $column->project_id)
                ->where('id', '!=', $column->id)
                ->where('is_done_column', true)
                ->exists();

            if (! $otherDone) {
                throw ValidationException::withMessages([
                    'is_done_column' => [__('errors.kanban_done_column_required')],
                ]);
            }
        }

        $column->update($data);

        return $column->fresh();
    }

    public function deleteColumn(KanbanColumn $column, string $targetColumnId): void
    {
        if ($column->is_done_column) {
            $otherDone = KanbanColumn::query()
                ->where('project_id', $column->project_id)
                ->where('id', '!=', $column->id)
                ->where('is_done_column', true)
                ->exists();

            if (! $otherDone) {
                throw ValidationException::withMessages([
                    'target_column_id' => [__('errors.kanban_done_column_required')],
                ]);
            }
        }

        $target = KanbanColumn::query()
            ->where('project_id', $column->project_id)
            ->findOrFail($targetColumnId);

        $column->tasks()->update([
            'kanban_column_id' => $target->id,
            'status' => TaskStatus::tryFrom($target->slug)?->value ?? $target->slug,
        ]);

        $column->delete();
    }

    /** @param list<string> $columnIds */
    public function reorderColumns(array $columnIds): void
    {
        foreach ($columnIds as $index => $columnId) {
            KanbanColumn::where('id', $columnId)->update([
                'order_index' => ($index + 1) * 1024,
            ]);
        }
    }

    public function assertWipLimit(KanbanColumn $column, ?string $excludeTaskId = null): void
    {
        if ($column->wip_limit === null) {
            return;
        }

        $query = $column->tasks();
        if ($excludeTaskId) {
            $query->where('id', '!=', $excludeTaskId);
        }

        if ($query->count() >= $column->wip_limit) {
            throw new HttpException(409, __('errors.kanban_wip_limit_reached'));
        }
    }

    public function resolveColumnForProject(Project $project, ?string $status = null, ?string $columnId = null): ?KanbanColumn
    {
        if ($columnId) {
            return KanbanColumn::query()
                ->where('project_id', $project->id)
                ->findOrFail($columnId);
        }

        $slug = $status ?? TaskStatus::Todo->value;

        return KanbanColumn::query()
            ->where('project_id', $project->id)
            ->where('slug', $slug)
            ->first();
    }

    public function syncTaskStatusFromColumn(KanbanColumn $column): TaskStatus
    {
        return TaskStatus::tryFrom($column->slug) ?? TaskStatus::Todo;
    }
}
