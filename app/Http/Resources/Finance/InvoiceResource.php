<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
class InvoiceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'invoice_number' => $this->invoice_number,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'tax_amount' => $this->tax_amount !== null ? (float) $this->tax_amount : null,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'file_path' => $this->file_path,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
