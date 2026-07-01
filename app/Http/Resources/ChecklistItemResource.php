<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ChecklistItem */
class ChecklistItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_done' => $this->is_done,
            'order_index' => (float) $this->order_index,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
