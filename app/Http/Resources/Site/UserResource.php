<?php

namespace App\Http\Resources\Site;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function __construct($resource, private readonly ?Membership $membership = null)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $membership = $this->membership ?? (app()->bound('membership') ? app('membership') : null);

        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'role' => $membership?->role?->value ?? 'MEMBER',
        ];
    }
}
