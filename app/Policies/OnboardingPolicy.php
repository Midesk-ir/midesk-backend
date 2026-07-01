<?php

namespace App\Policies;

use App\Models\HrSetting;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class OnboardingPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageHr();
    }

    public function manage(User $user): bool
    {
        return $this->canManageHr();
    }

    public function viewOwn(User $user, User $target): bool
    {
        return $user->id === $target->id || $this->canManageHr();
    }
}
