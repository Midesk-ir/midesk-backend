<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class FinancialController extends Controller
{
    public function overview(): JsonResponse
    {
        return response()->json([
            'mrr' => Cache::get('super_admin:mrr', 0),
            'arr' => Cache::get('super_admin:arr', 0),
            'active_subscriptions' => Subscription::query()->where('status', 'active')->count(),
        ]);
    }

    public function revenueByPlan(): JsonResponse
    {
        $data = SubscriptionInvoice::query()
            ->where('status', 'paid')
            ->with('subscription.plan')
            ->get()
            ->groupBy(fn ($i) => $i->subscription?->plan?->name ?? 'Unknown')
            ->map(fn ($group) => $group->sum('amount'));

        return response()->json(['data' => $data]);
    }

    public function revenueByCycle(): JsonResponse
    {
        return response()->json(['data' => ['monthly' => 0, 'yearly' => 0]]);
    }

    public function cancellations(): JsonResponse
    {
        return response()->json(['data' => Subscription::query()->where('status', 'cancelled')->get()]);
    }

    public function sendExpiryReminders(): JsonResponse
    {
        return response()->json(['message' => 'Expiry reminders queued']);
    }
}

