<?php

namespace App\Jobs;

use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckSubscriptionExpiry implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Subscription::query()
            ->where('status', 'active')
            ->where('current_period_end', '<', now())
            ->each(function (Subscription $subscription): void {
                Log::warning('Subscription expired', ['subscription_id' => $subscription->id]);
                $subscription->update(['status' => 'expired']);
            });
    }
}
