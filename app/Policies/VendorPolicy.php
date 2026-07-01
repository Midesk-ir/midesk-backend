<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }
}
