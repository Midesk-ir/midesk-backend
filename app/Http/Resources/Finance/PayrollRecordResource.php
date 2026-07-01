<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PayrollRecord */
class PayrollRecordResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'period' => $this->period,
            'contract_id' => $this->contract_id,
            'base_salary' => (float) $this->base_salary,
            'overtime_hours' => (float) $this->overtime_hours,
            'overtime_amount' => (float) $this->overtime_amount,
            'allowances' => $this->allowances,
            'deductions' => $this->deductions,
            'gross_amount' => (float) $this->gross_amount,
            'net_amount' => (float) $this->net_amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'calculated_at' => $this->calculated_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
            ]),
            'contract' => $this->whenLoaded('contract', fn () => [
                'id' => $this->contract->id,
                'type' => $this->contract->type->value,
                'base_salary' => (float) $this->contract->base_salary,
            ]),
        ];
    }
}
