<?php

namespace App\Http\Middleware;

use App\Support\PlanFeatureGate;
use Closure;
use Illuminate\Http\Request;

class CheckPlanAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (! app()->bound('tenant')) {
            return $next($request);
        }

        $tenant = app('tenant');
        $module = PlanFeatureGate::routeModule($request->getPathInfo());

        if ($module && ! PlanFeatureGate::allowsModule($tenant, $module)) {
            return response()->json([
                'error' => [
                    'code' => 'PLAN_LIMIT',
                    'message' => __('errors.forbidden'),
                    'details' => [
                        ['field' => 'plan', 'msg' => "Module [{$module}] is not available in current plan."],
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}

