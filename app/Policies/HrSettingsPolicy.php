<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class HrSettingsPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageHr();
    }

    public function updateAny(User $user): bool
    {
        return $this->canManageHr();
    }

    public function view(User $user, HrSetting $setting): bool
    {
        return $this->canManageHr();
    }

    public function update(User $user, HrSetting $setting): bool
    {
        return $this->canManageHr();
    }
}
