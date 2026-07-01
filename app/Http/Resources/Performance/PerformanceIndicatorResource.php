<?php

namespace App\Http\Resources\Performance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PerformanceIndicator */
class PerformanceIndicatorResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category?->value,
            'weight' => (float) $this->weight,
            'measurement_unit' => $this->measurement_unit,
            'target_value' => $this->target_value !== null ? (float) $this->target_value : null,
            'data_source' => $this->data_source?->value,
            'is_active' => $this->is_active,
        ];
    }
}
