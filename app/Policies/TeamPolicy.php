<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function view(User $user, Team $team): bool
    {
        return app('membership') !== null;
    }

    public function create(User $user): bool
    {
        return app('membership')?->isOwnerOrAdmin() ?? false;
    }

    public function update(User $user, Team $team): bool
    {
        return app('membership')?->isOwnerOrAdmin() ?? false;
    }

    public function delete(User $user, Team $team): bool
    {
        return app('membership')?->isOwnerOrAdmin() ?? false;
    }

    public function manageMembers(User $user, Team $team): bool
    {
        return app('membership')?->isOwnerOrAdmin() ?? false;
    }
}
