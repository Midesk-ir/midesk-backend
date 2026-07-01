<?php

namespace App\Policies;

use App\Enums\ApprovalWorkflowStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Support\ApprovalWorkflow;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->user_id === $user->id
            || app('membership')?->canManageFinance()
            || app('membership')?->isManagerOrAbove();
    }

    public function create(User $user): bool
    {
        return app('membership') !== null;
    }

    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->user_id === $user->id
            && $purchaseRequest->status === ApprovalWorkflowStatus::Draft;
    }

    public function submit(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->user_id === $user->id;
    }

    public function ownerReview(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return ApprovalWorkflow::canManagerReview($user, $purchaseRequest->user, app('membership'));
    }

    public function financeReview(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return app('membership')?->canManageFinance() ?? false;
    }

    public function cancel(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->user_id === $user->id
            || app('membership')?->canManageFinance();
    }
}
