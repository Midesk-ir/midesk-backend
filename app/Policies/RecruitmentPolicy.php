<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class RecruitmentPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageHr() || $this->isOwnerOrAdmin() || $this->membership()?->role->value === 'MANAGER';
    }

    public function manage(User $user): bool
    {
        return $this->canManageHr();
    }
}
