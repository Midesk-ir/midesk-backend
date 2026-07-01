<?php

namespace App\Policies;

use App\Models\LeavePolicy;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class LeavePolicyPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageHr();
    }

    public function create(User $user): bool
    {
        return $this->canManageHr();
    }

    public function update(User $user, LeavePolicy $policy): bool
    {
        return $this->canManageHr();
    }

    public function delete(User $user, LeavePolicy $policy): bool
    {
        return $this->canManageHr();
    }

    public function report(User $user): bool
    {
        return $this->isManagerOrAbove();
    }
}
