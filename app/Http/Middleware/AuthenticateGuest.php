<?php

namespace App\Http\Middleware;

use App\Models\GuestAccess;
use App\Services\GuestAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateGuest
{
    public function __construct(private GuestAccessService $guestService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Guest-Token') ?? $request->query('guest_token');

        if (! $token) {
            return response()->json(['error' => ['code' => 'GUEST_TOKEN_REQUIRED', 'message' => 'Guest token required']], 401);
        }

        $guest = $this->guestService->resolveByToken($token);
        if (! $guest) {
            return response()->json(['error' => ['code' => 'GUEST_TOKEN_INVALID', 'message' => 'Invalid or expired guest token']], 401);
        }

        app()->instance('guest', $guest);
        app()->instance('tenant', $guest->tenant);

        return $next($request);
    }
}
