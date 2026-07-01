<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Requisition */
class RequisitionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'department' => $this->department,
            'positions_count' => $this->positions_count,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'created_by_user_id' => $this->created_by_user_id,
        ];
    }
}
