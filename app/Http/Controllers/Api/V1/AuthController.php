<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginOtpRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\Site\UserResource;
use App\Services\AuthService;
use App\Services\SiteCompatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SiteCompatService $siteCompat,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtpAndRegister($request->validated());

        return response()->json($this->siteAuthResponse($result['user'], $result['token']));
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json($this->siteAuthResponse($result['user'], $result['token']));
    }

    public function loginOtp(LoginOtpRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['code'])) {
            return response()->json($this->authService->sendLoginOtp($data));
        }

        $result = $this->authService->loginWithOtp($data);

        return response()->json($this->siteAuthResponse($result['user'], $result['token']));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => __('auth.logged_out')]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return response()->json($this->authService->forgotPassword($request->validated('email')));
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        return response()->json($this->authService->resetPassword($request->validated()));
    }

    public function googleRedirect(): JsonResponse
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function googleCallback(): JsonResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = \App\Models\User::query()->firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'full_name' => $googleUser->getName() ?? $googleUser->getEmail(),
                'is_active' => true,
            ]
        );

        $user->update(['last_login_at' => now()]);

        $result = $this->authService->createTokenResponse($user);

        return response()->json($this->siteAuthResponse($result['user'], $result['token']));
    }

    /** @return array<string, mixed> */
    private function siteAuthResponse(\App\Models\User $user, string $token): array
    {
        $payload = $this->siteCompat->authPayload($user);
        $payload['token'] = $token;

        return $payload;
    }
}
