<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BootstrapResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource['user']),
            'tenant' => new TenantResource($this->resource['tenant']),
            'membership' => new MembershipResource($this->resource['membership']),
            'tenants' => TenantResource::collection($this->resource['tenants']),
            'unread_notifications_count' => $this->resource['unread_notifications_count'],
            'locale' => $this->resource['locale'],
        ];
    }
}
