<?php

namespace App\Policies;

use App\Models\Objective;
use App\Models\User;

class ObjectivePolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function view(User $user, Objective $objective): bool
    {
        return $objective->user_id === $user->id
            || app('membership')?->isManagerOrAbove();
    }

    public function create(User $user): bool
    {
        return app('membership') !== null;
    }

    public function update(User $user, Objective $objective): bool
    {
        return $objective->user_id === $user->id
            || app('membership')?->isManagerOrAbove();
    }

    public function delete(User $user, Objective $objective): bool
    {
        return $this->update($user, $objective);
    }
}
