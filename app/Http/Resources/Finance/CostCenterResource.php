<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CostCenter */
class CostCenterResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'parent_id' => $this->parent_id,
            'manager_user_id' => $this->manager_user_id,
            'parent' => new CostCenterResource($this->whenLoaded('parent')),
            'manager' => $this->whenLoaded('manager', fn () => [
                'id' => $this->manager->id,
                'full_name' => $this->manager->full_name,
                'email' => $this->manager->email,
            ]),
        ];
    }
}
