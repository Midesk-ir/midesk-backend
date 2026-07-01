<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageHr() ?? false;
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $this->create($user);
    }
}
