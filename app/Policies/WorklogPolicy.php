<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Worklog;

class WorklogPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function viewTeam(User $user): bool
    {
        return app('membership')?->isManagerOrAbove() ?? false;
    }

    public function create(User $user): bool
    {
        return app('membership') !== null;
    }

    public function update(User $user, Worklog $worklog): bool
    {
        if ($worklog->user_id === $user->id) {
            return true;
        }

        return app('membership')?->isOwnerOrAdmin() ?? false;
    }

    public function delete(User $user, Worklog $worklog): bool
    {
        return $this->update($user, $worklog);
    }
}
