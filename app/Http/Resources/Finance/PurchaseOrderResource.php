<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PurchaseOrder */
class PurchaseOrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_request_id' => $this->purchase_request_id,
            'vendor_id' => $this->vendor_id,
            'items' => $this->items,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'ordered_at' => $this->ordered_at?->toIso8601String(),
            'received_at' => $this->received_at?->toIso8601String(),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'purchase_request' => new PurchaseRequestResource($this->whenLoaded('purchaseRequest')),
        ];
    }
}
