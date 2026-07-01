<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tenant */
class TenantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'region' => $this->region?->value,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'default_currency' => $this->default_currency,
            'calendar_type' => $this->calendar_type?->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
