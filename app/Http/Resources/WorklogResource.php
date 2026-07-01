<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Worklog */
class WorklogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'task_id' => $this->task_id,
            'task' => new TaskResource($this->whenLoaded('task')),
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'logged_date' => $this->logged_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
