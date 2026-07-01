<?php

namespace App\Policies;

use App\Models\PayrollRecord;
use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership')?->canApprovePayroll() ?? false;
    }

    public function view(User $user, PayrollRecord $record): bool
    {
        return $record->user_id === $user->id
            || app('membership')?->canApprovePayroll();
    }

    public function calculate(User $user): bool
    {
        return app('membership')?->canApprovePayroll() ?? false;
    }

    public function approve(User $user, PayrollRecord $record): bool
    {
        return app('membership')?->canApprovePayroll() ?? false;
    }

    public function bulkPay(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }
}
