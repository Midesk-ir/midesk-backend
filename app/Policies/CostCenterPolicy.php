<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\CostCenter;
use App\Models\User;

class CostCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewFinance($user);
    }

    public function view(User $user, CostCenter $costCenter): bool
    {
        return $this->canViewFinance($user);
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function update(User $user, CostCenter $costCenter): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function delete(User $user, CostCenter $costCenter): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    private function canViewFinance(User $user): bool
    {
        $membership = app('membership');

        return $membership?->canManageFinance()
            || $membership?->isManagerOrAbove()
            || $membership !== null;
    }
}
