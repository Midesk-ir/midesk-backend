<?php

namespace App\Policies;

use App\Models\PerformanceReview;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function view(User $user, PerformanceReview $review): bool
    {
        return $review->user_id === $user->id
            || $review->reviewer_user_id === $user->id
            || app('membership')?->canManageHr();
    }

    public function create(User $user): bool
    {
        return app('membership')?->canManageHr() ?? false;
    }

    public function update(User $user, PerformanceReview $review): bool
    {
        return app('membership')?->canManageHr() ?? false;
    }

    public function submitSelf(User $user, PerformanceReview $review): bool
    {
        return $review->user_id === $user->id;
    }

    public function submitManager(User $user, PerformanceReview $review): bool
    {
        return app('membership')?->isManagerOrAbove() ?? false;
    }

    public function finalize(User $user, PerformanceReview $review): bool
    {
        return app('membership')?->canManageHr() ?? false;
    }
}
