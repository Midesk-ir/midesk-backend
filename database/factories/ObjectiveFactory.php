<?php

namespace Database\Factories;

use App\Models\Objective;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Objective> */
class ObjectiveFactory extends Factory
{
    protected $model = Objective::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
