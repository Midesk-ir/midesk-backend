<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CalculateMRR implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $mrr = SubscriptionInvoice::query()
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonth())
            ->sum('amount');

        Cache::put('super_admin:mrr', $mrr, now()->addDay());
        Cache::put('super_admin:arr', $mrr * 12, now()->addDay());
        Cache::put('super_admin:active_subs', Subscription::query()->where('status', 'active')->count(), now()->addDay());
    }
}
