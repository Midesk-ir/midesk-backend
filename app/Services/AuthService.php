<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    public function register(array $data): array
    {
        $identifier = $data['email'] ?? $data['phone'];

        $this->otpService->generate($identifier, 'register');

        return [
            'message' => __('auth.otp_sent'),
            'identifier' => $identifier,
        ];
    }

    public function verifyOtpAndRegister(array $data): array
    {
        $identifier = $data['email'] ?? $data['phone'];

        if (! $this->otpService->verify($identifier, $data['code'], 'register')) {
            throw ValidationException::withMessages([
                'code' => [__('auth.invalid_otp')],
            ]);
        }

        $user = User::query()
            ->when(! empty($data['email']), fn ($q) => $q->where('email', $data['email']))
            ->when(! empty($data['phone']), fn ($q) => $q->orWhere('phone', $data['phone']))
            ->first();

        if (! $user) {
            $resolvedName = $data['full_name'] ?? $data['name'] ?? null;
            if (! $resolvedName) {
                $resolvedName = $data['email'] ?? $data['phone'] ?? 'New User';
            }

            $user = User::create([
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'full_name' => $resolvedName,
                'password' => isset($data['password']) ? $data['password'] : null,
                'is_active' => true,
            ]);
        }

        return $this->createTokenResponse($user);
    }

    public function login(array $data): array
    {
        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password ?? '')) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => [__('auth.inactive')],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        return $this->createTokenResponse($user);
    }

    public function loginWithOtp(array $data): array
    {
        $identifier = $data['phone'] ?? $data['email'];

        if (! $this->otpService->verify($identifier, $data['code'], 'login')) {
            throw ValidationException::withMessages([
                'code' => [__('auth.invalid_otp')],
            ]);
        }

        $user = User::query()
            ->when(! empty($data['phone']), fn ($q) => $q->where('phone', $data['phone']))
            ->when(empty($data['phone']) && ! empty($data['email']), fn ($q) => $q->where('email', $data['email']))
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => [__('auth.user_not_found')],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        return $this->createTokenResponse($user);
    }

    public function sendLoginOtp(array $data): array
    {
        $identifier = $data['phone'] ?? $data['email'];

        $user = User::query()
            ->when(! empty($data['phone']), fn ($q) => $q->where('phone', $data['phone']))
            ->when(empty($data['phone']) && ! empty($data['email']), fn ($q) => $q->where('email', $data['email']))
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => [__('auth.user_not_found')],
            ]);
        }

        $this->otpService->generate($identifier, 'login');

        return [
            'message' => __('auth.otp_sent'),
            'identifier' => $identifier,
        ];
    }

    public function forgotPassword(string $email): array
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ['message' => __('auth.reset_link_sent')];
    }

    public function resetPassword(array $data): array
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $user->update(['password' => $password]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ['message' => __('auth.password_reset')];
    }

    /** @return array{user: User, token: string}> */
    public function createTokenResponse(User $user, ?string $tenantId = null): array
    {
        $abilities = $tenantId ? ['tenant_id' => $tenantId] : [];

        $token = $user->createToken('api', $abilities)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
