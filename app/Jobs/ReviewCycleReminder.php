<?php

namespace App\Jobs;

use App\Models\Membership;
use App\Models\PerformanceReview;
use App\Models\Tenant;
use App\Notifications\ReviewCycleReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class ReviewCycleReminder implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $period = now()->format('Y').'-Q'.ceil(now()->month / 3);
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            app()->instance('tenant', $tenant);

            $pendingReviews = PerformanceReview::query()
                ->where('period', $period)
                ->whereNotIn('status', ['finalized'])
                ->with('user')
                ->get();

            foreach ($pendingReviews as $review) {
                if ($review->user) {
                    Notification::send($review->user, new ReviewCycleReminderNotification($review));
                }
            }

            $hrUsers = Membership::query()
                ->where('tenant_id', $tenant->id)
                ->whereIn('role', ['OWNER', 'ADMIN', 'HR_MANAGER'])
                ->where('status', 'active')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            Notification::send($hrUsers, new ReviewCycleReminderNotification(null, $period));

            app()->forgetInstance('tenant');
        }
    }
}
