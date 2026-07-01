<?php

namespace App\Policies;

use App\Models\KeyResult;
use App\Models\User;

class KeyResultPolicy
{
    public function update(User $user, KeyResult $keyResult): bool
    {
        return $keyResult->objective->user_id === $user->id
            || app('membership')?->isManagerOrAbove();
    }
}
