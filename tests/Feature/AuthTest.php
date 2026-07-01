<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_can_register_and_verify_otp(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'email' => 'newuser@example.com',
        ])->assertCreated()
            ->assertJsonPath('message', __('auth.otp_sent'));

        $otp = OtpCode::query()->where('identifier', 'newuser@example.com')->latest()->first();

        $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'newuser@example.com',
            'code' => $otp->code,
            'full_name' => 'New User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertOk()
            ->assertJsonStructure(['user' => ['id', 'email', 'full_name'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_user_can_login_with_email_and_password(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }
}
