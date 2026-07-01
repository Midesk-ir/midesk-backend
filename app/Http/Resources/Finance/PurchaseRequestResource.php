<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PurchaseRequest */
class PurchaseRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'items' => $this->items,
            'total_amount' => (float) $this->total_amount,
            'currency' => $this->currency,
            'cost_center_id' => $this->cost_center_id,
            'budget_id' => $this->budget_id,
            'justification' => $this->justification,
            'status' => $this->status->value,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
            ]),
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'budget' => new BudgetResource($this->whenLoaded('budget')),
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
        ];
    }
}
