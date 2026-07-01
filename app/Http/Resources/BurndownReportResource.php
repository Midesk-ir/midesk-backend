<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BurndownReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'sprint' => $this->resource['sprint'],
            'daily' => $this->resource['daily'],
            'ideal_line' => $this->resource['ideal_line'],
        ];
    }
}
