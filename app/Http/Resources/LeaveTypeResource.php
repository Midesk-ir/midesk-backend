<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\LeaveType */
class LeaveTypeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'default_balance_days' => (float) $this->default_balance_days,
            'unit' => $this->unit?->value,
            'is_paid' => $this->is_paid,
            'requires_approval' => $this->requires_approval,
            'max_consecutive_days' => $this->max_consecutive_days,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
