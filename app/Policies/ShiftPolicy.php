<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function create(User $user): bool
    {
        return app('membership')?->isOwnerOrAdmin() ?? false;
    }

    public function update(User $user, Shift $shift): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $this->create($user);
    }
}
