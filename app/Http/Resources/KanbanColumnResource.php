<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\KanbanColumn */
class KanbanColumnResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'order_index' => (float) $this->order_index,
            'wip_limit' => $this->wip_limit,
            'is_done_column' => $this->is_done_column,
            'current_count' => $this->when(isset($this->current_count), $this->current_count),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
