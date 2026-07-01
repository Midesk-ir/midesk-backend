<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VelocityReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'sprints' => $this->resource['sprints'],
            'average_velocity' => $this->resource['average_velocity'],
        ];
    }
}
