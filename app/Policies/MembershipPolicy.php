<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function invite(User $user): bool
    {
        return app('membership')?->isOwnerOrAdmin() ?? false;
    }

    public function update(User $user, Membership $membership): bool
    {
        $current = app('membership');

        if (! $current?->isOwnerOrAdmin()) {
            return false;
        }

        if ($membership->role === MembershipRole::Owner || request()->input('role') === MembershipRole::Owner->value) {
            return $current->role === MembershipRole::Owner;
        }

        return true;
    }

    public function delete(User $user, Membership $membership): bool
    {
        if ($membership->role === MembershipRole::Owner) {
            return false;
        }

        return app('membership')?->isOwnerOrAdmin() ?? false;
    }
}
