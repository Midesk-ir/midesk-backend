<?php

namespace App\Services;

use App\Models\GuestAccess;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class GuestAccessService
{
    public function invite(User $creator, array $data): GuestAccess
    {
        return GuestAccess::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'token' => Str::random(64),
            'role' => $data['role'] ?? 'viewer',
            'project_ids' => $data['project_ids'] ?? [],
            'expires_at' => $data['expires_at'],
            'created_by_user_id' => $creator->id,
            'created_at' => now(),
        ]);
    }

    public function resolveByToken(string $token): ?GuestAccess
    {
        $guest = GuestAccess::withoutGlobalScopes()
            ->where('token', $token)
            ->first();

        if (! $guest || $guest->isExpired()) {
            return null;
        }

        $guest->update(['last_accessed_at' => now()]);

        return $guest;
    }

    public function projectsForGuest(GuestAccess $guest)
    {
        return Project::withoutGlobalScopes()
            ->where('tenant_id', $guest->tenant_id)
            ->whereIn('id', $guest->project_ids ?? [])
            ->get();
    }
}
