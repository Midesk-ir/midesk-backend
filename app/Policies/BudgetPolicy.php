<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership')?->canManageFinance()
            || app('membership')?->isManagerOrAbove()
            || app('membership') !== null;
    }

    public function view(User $user, Budget $budget): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function update(User $user, Budget $budget): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }
}
