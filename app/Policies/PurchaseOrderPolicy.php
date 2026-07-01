<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership')?->canManageFinance()
            || app('membership')?->isManagerOrAbove();
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }
}
