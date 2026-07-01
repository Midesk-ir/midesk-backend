<?php

namespace App\Policies;

use App\Models\Mission;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class MissionPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->membership() !== null;
    }

    public function view(User $user, Mission $mission): bool
    {
        return $mission->user_id === $user->id || $this->isManagerOrAbove();
    }

    public function create(User $user): bool
    {
        return $this->membership() !== null;
    }

    public function approve(User $user, Mission $mission): bool
    {
        if ($this->canManageHr() || $this->isOwnerOrAdmin()) {
            return true;
        }

        return $this->membership()?->role->value === 'MANAGER'
            && $this->isTeamMember($user, $mission->user);
    }
}
