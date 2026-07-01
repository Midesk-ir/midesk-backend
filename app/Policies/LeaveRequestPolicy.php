<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function view(User $user, LeaveRequest $request): bool
    {
        if ($request->user_id === $user->id) {
            return true;
        }

        return app('membership')?->isManagerOrAbove() ?? false;
    }

    public function create(User $user): bool
    {
        return app('membership') !== null;
    }

    public function review(User $user, LeaveRequest $request): bool
    {
        return app('membership')?->isManagerOrAbove() ?? false;
    }

    public function cancel(User $user, LeaveRequest $request): bool
    {
        return $request->user_id === $user->id
            || app('membership')?->isManagerOrAbove();
    }
}
