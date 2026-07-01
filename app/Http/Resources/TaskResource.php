<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Task */
class TaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status?->value,
            'kanban_column_id' => $this->kanban_column_id,
            'kanban_column' => new KanbanColumnResource($this->whenLoaded('kanbanColumn')),
            'sprint_id' => $this->sprint_id,
            'sprint' => new SprintResource($this->whenLoaded('sprint')),
            'story_points' => $this->story_points,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'priority' => $this->priority?->value,
            'assignee_user_id' => $this->assignee_user_id,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'reporter_user_id' => $this->reporter_user_id,
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'due_date' => $this->due_date?->toIso8601String(),
            'order_index' => (float) $this->order_index,
            'parent_task_id' => $this->parent_task_id,
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'checklist' => ChecklistItemResource::collection($this->whenLoaded('checklistItems')),
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments')),
            'comment_count' => $this->whenCounted('comments'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
