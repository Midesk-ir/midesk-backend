<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    protected $model = User::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'full_name' => fake()->name(),
            'password' => static::$password ??= 'password',
            'avatar_url' => null,
            'is_active' => true,
            'last_login_at' => null,
        ];
    }
}
