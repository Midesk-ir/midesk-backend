<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Sprint */
class SprintResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $stats = $this->sprintStats();

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'goal' => $this->goal,
            'status' => $this->status?->value,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_points' => $stats['total_points'],
            'completed_points' => $stats['completed_points'],
            'tasks_count' => $stats['tasks_count'],
            'done_count' => $stats['done_count'],
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /** @return array{total_points: int, completed_points: int, tasks_count: int, done_count: int} */
    private function sprintStats(): array
    {
        if ($this->relationLoaded('tasks')) {
            $doneTasks = $this->tasks->filter(
                fn ($task) => (bool) $task->kanbanColumn?->is_done_column
            );

            return [
                'total_points' => (int) $this->tasks->sum(fn ($task) => (int) ($task->story_points ?? 0)),
                'completed_points' => (int) $doneTasks->sum(fn ($task) => (int) ($task->story_points ?? 0)),
                'tasks_count' => $this->tasks->count(),
                'done_count' => $doneTasks->count(),
            ];
        }

        return [
            'total_points' => (int) ($this->total_points_sum ?? $this->tasks_sum_story_points ?? 0),
            'completed_points' => (int) ($this->completed_points_sum ?? 0),
            'tasks_count' => (int) ($this->tasks_count ?? 0),
            'done_count' => (int) ($this->done_count ?? 0),
        ];
    }
}
