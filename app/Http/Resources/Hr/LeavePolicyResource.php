<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\LeavePolicy */
class LeavePolicyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'leave_type_id' => $this->leave_type_id,
            'role' => $this->role?->value,
            'auto_approve' => $this->auto_approve,
            'approval_chain' => $this->approval_chain,
            'min_notice_days' => $this->min_notice_days,
            'allow_negative_balance' => $this->allow_negative_balance,
            'prorate_first_year' => $this->prorate_first_year,
            'leave_type' => $this->whenLoaded('leaveType', fn () => [
                'id' => $this->leaveType->id,
                'name' => $this->leaveType->name,
            ]),
        ];
    }
}
