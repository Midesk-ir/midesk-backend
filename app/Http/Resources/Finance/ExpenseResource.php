<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Expense */
class ExpenseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'cost_center_id' => $this->cost_center_id,
            'budget_id' => $this->budget_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'receipt_path' => $this->receipt_path,
            'status' => $this->status->value,
            'rejection_reason' => $this->rejection_reason,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
            ]),
            'category' => new ExpenseCategoryResource($this->whenLoaded('category')),
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'budget' => new BudgetResource($this->whenLoaded('budget')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
