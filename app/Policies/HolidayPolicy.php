<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function create(User $user): bool
    {
        return app('membership')?->isOwnerOrAdmin() ?? false;
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $this->create($user);
    }
}
