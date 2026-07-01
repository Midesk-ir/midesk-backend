<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payment */
class PaymentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'method' => $this->method->value,
            'reference' => $this->reference,
            'paid_by_user_id' => $this->paid_by_user_id,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'receipt_path' => $this->receipt_path,
            'paid_by' => $this->whenLoaded('paidBy', fn () => [
                'id' => $this->paidBy->id,
                'full_name' => $this->paidBy->full_name,
            ]),
        ];
    }
}
