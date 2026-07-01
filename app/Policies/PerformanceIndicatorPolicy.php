<?php

namespace App\Policies;

use App\Models\PerformanceIndicator;
use App\Models\User;

class PerformanceIndicatorPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageHr() ?? false;
    }

    public function update(User $user, PerformanceIndicator $indicator): bool
    {
        return app('membership')?->canManageHr() ?? false;
    }

    public function delete(User $user, PerformanceIndicator $indicator): bool
    {
        return app('membership')?->canManageHr() ?? false;
    }
}
