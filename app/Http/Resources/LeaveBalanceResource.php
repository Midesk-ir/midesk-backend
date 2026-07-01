<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\LeaveBalance */
class LeaveBalanceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'leave_type_id' => $this->leave_type_id,
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'year' => $this->year,
            'total' => (float) $this->total,
            'used' => (float) $this->used,
            'remaining' => (float) $this->remaining,
            'created_at' => $this->created_at instanceof \DateTimeInterface
                ? $this->created_at->format(\DateTimeInterface::ATOM)
                : $this->created_at,
        ];
    }
}
