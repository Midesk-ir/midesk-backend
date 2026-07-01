<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class ContractPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->membership() !== null;
    }

    public function view(User $user, Contract $contract): bool
    {
        if ($this->canManageHr()) {
            return true;
        }

        if ($contract->user_id === $user->id) {
            return true;
        }

        if ($this->membership()?->role->value === 'MANAGER') {
            return $this->isTeamMember($user, $contract->user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->canManageHr();
    }

    public function update(User $user, Contract $contract): bool
    {
        return $this->canManageHr();
    }
}
