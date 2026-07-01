<?php

namespace App\Policies;

use App\Enums\ApprovalWorkflowStatus;
use App\Models\Expense;
use App\Models\Membership;
use App\Models\User;
use App\Support\ApprovalWorkflow;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function view(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id
            || app('membership')?->canManageFinance()
            || app('membership')?->isManagerOrAbove();
    }

    public function create(User $user): bool
    {
        return app('membership') !== null;
    }

    public function update(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id
            && in_array($expense->status, [ApprovalWorkflowStatus::Draft, ApprovalWorkflowStatus::Rejected], true);
    }

    public function submit(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id;
    }

    public function approve(User $user, Expense $expense): bool
    {
        $membership = $this->resolveMembership($user);

        if (! $membership) {
            return false;
        }

        if ($membership->isOwnerOrAdmin() || $membership->canManageFinance()) {
            return in_array($expense->status, [
                ApprovalWorkflowStatus::Submitted,
                ApprovalWorkflowStatus::ManagerReview,
                ApprovalWorkflowStatus::FinanceReview,
            ], true);
        }

        return ApprovalWorkflow::canManagerReview($user, $expense->user, $membership);
    }

    public function reject(User $user, Expense $expense): bool
    {
        return $this->approve($user, $expense);
    }

    public function pay(User $user, Expense $expense): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function manageCategories(User $user): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    private function resolveMembership(User $user): ?Membership
    {
        if (app()->bound('membership') && ($membership = app('membership'))) {
            return $membership;
        }

        $tenantId = app()->bound('tenant')
            ? app('tenant')->id
            : request()->header('X-Tenant-Id');

        if (! $tenantId) {
            return null;
        }

        return Membership::query()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
    }
}
