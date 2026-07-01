<?php

namespace App\Http\Middleware;

use App\Models\Membership;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenantId = $request->header('X-Tenant-Id');

        if (! $tenantId && $user?->currentAccessToken()?->abilities) {
            $abilities = $user->currentAccessToken()->abilities;
            $tenantId = $abilities['tenant_id'] ?? null;
        }

        if (! $tenantId) {
            return response()->json([
                'error' => [
                    'code' => 'TENANT_REQUIRED',
                    'message' => __('errors.tenant_required'),
                ],
            ], 400);
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            return response()->json([
                'error' => [
                    'code' => 'TENANT_NOT_FOUND',
                    'message' => __('errors.tenant_not_found'),
                ],
            ], 404);
        }

        $membership = Membership::query()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $membership) {
            return response()->json([
                'error' => [
                    'code' => 'TENANT_ACCESS_DENIED',
                    'message' => __('errors.tenant_access_denied'),
                ],
            ], 403);
        }

        app()->instance('tenant', $tenant);
        app()->instance('membership', $membership);

        return $next($request);
    }
}
