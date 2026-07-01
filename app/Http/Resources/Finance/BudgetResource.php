<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Budget */
class BudgetResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cost_center_id' => $this->cost_center_id,
            'year' => $this->year,
            'quarter' => $this->quarter,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'spent' => number_format((float) $this->spent, 2, '.', ''),
            'currency' => $this->currency,
            'status' => $this->status->value,
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
        ];
    }
}
