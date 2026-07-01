<?php

namespace App\Policies;

use App\Models\PerformanceScore;
use App\Models\User;
use App\Support\ApprovalWorkflow;

class PerformanceScorePolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageHr()
            || app('membership')?->isManagerOrAbove();
    }
}
