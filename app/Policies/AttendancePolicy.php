<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class AttendancePolicy
{
    use AuthorizesHrAccess;

    public function checkIn(User $user): bool
    {
        return $this->membership() !== null;
    }

    public function viewAny(User $user): bool
    {
        return $this->membership() !== null;
    }

    public function viewTeam(User $user): bool
    {
        return $this->isManagerOrAbove();
    }

    public function view(User $user, AttendanceRecord $record): bool
    {
        if ($record->user_id === $user->id) {
            return true;
        }

        if ($this->canManageHr()) {
            return true;
        }

        if ($this->membership()?->role->value === 'MANAGER') {
            return $this->isTeamMember($user, $record->user);
        }

        return false;
    }

    public function update(User $user, AttendanceRecord $record): bool
    {
        return $this->canManageHr() || $this->isOwnerOrAdmin();
    }
}
