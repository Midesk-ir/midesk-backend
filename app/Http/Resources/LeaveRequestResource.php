<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\LeaveRequest */
class LeaveRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'leave_type_id' => $this->leave_type_id,
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration_days' => (float) $this->duration_days,
            'status' => $this->status?->value,
            'note' => $this->note,
            'reviewed_by_user_id' => $this->reviewed_by_user_id,
            'reviewed_by' => new UserResource($this->whenLoaded('reviewedBy')),
            'reviewed_at' => $this->reviewed_at instanceof \DateTimeInterface
                ? $this->reviewed_at->format(\DateTimeInterface::ATOM)
                : $this->reviewed_at,
            'created_at' => $this->when(isset($this->created_at), function () {
                return $this->created_at instanceof \DateTimeInterface
                    ? $this->created_at->format(\DateTimeInterface::ATOM)
                    : $this->created_at;
            }),
        ];
    }
}
