<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AttendanceRecord */
class AttendanceRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'date' => $this->date->toDateString(),
            'check_in_at' => $this->check_in_at?->toIso8601String(),
            'check_out_at' => $this->check_out_at?->toIso8601String(),
            'break_start_at' => $this->break_start_at?->toIso8601String(),
            'break_end_at' => $this->break_end_at?->toIso8601String(),
            'status' => $this->status->value,
            'auto_checkout' => $this->auto_checkout,
            'effective_minutes' => $this->effective_minutes,
            'overtime_minutes' => $this->overtime_minutes,
            'notes' => $this->notes,
            'shift' => $this->whenLoaded('shift', fn () => $this->shift ? [
                'id' => $this->shift->id,
                'name' => $this->shift->name,
                'start_time' => substr((string) $this->shift->start_time, 0, 5),
                'end_time' => substr((string) $this->shift->end_time, 0, 5),
            ] : null),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
            ]),
        ];
    }
}
