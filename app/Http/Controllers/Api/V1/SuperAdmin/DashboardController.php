<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'tenants_count' => Tenant::count(),
            'mrr' => Cache::get('super_admin:mrr', 0),
            'arr' => Cache::get('super_admin:arr', 0),
            'active_subscriptions' => Cache::get('super_admin:active_subs', Subscription::where('status', 'active')->count()),
        ]);
    }
}

