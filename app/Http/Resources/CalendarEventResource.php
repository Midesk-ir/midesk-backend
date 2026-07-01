<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CalendarEvent */
class CalendarEventResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'all_day' => $this->all_day,
            'color' => $this->color,
            'recurrence_rule' => $this->recurrence_rule,
            'created_by_user_id' => $this->created_by_user_id,
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
