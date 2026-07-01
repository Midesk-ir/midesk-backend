<?php

namespace App\Policies;

use App\Models\Personnel;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class PersonnelPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->membership() !== null;
    }

    public function view(User $user, Personnel $personnel): bool
    {
        if ($this->canManageHr()) {
            return true;
        }

        if ($this->membership()?->role->value === 'MANAGER') {
            return $this->isTeamMember($user, $personnel->user);
        }

        return $personnel->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->canManageHr();
    }

    public function update(User $user, Personnel $personnel): bool
    {
        return $this->canManageHr();
    }

    public function delete(User $user, Personnel $personnel): bool
    {
        return $this->canManageHr();
    }
}
