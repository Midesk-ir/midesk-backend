<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof \App\Models\SuperAdmin || ! $user->is_active) {
            return response()->json(['error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Super admin authentication required']], 401);
        }

        return $next($request);
    }
}
