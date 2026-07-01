<?php

namespace App\Http\Resources\Performance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\KeyResult */
class KeyResultResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'objective_id' => $this->objective_id,
            'title' => $this->title,
            'target_value' => (float) $this->target_value,
            'current_value' => (float) $this->current_value,
            'unit' => $this->unit,
            'weight' => (float) $this->weight,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
