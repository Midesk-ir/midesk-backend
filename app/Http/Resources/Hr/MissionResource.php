<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Mission */
class MissionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'destination' => $this->destination,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'transport_type' => $this->transport_type->value,
            'description' => $this->description,
            'status' => $this->status->value,
            'approved_by_user_id' => $this->approved_by_user_id,
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
