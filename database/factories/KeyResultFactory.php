<?php

namespace Database\Factories;

use App\Models\KeyResult;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<KeyResult> */
class KeyResultFactory extends Factory
{
    protected $model = KeyResult::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
