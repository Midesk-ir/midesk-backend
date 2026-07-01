<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }
}
