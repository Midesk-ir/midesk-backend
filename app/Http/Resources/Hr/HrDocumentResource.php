<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\HrDocument */
class HrDocumentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category' => $this->category->value,
            'title' => $this->title,
            'file_size' => $this->file_size,
            'uploaded_by_user_id' => $this->uploaded_by_user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
