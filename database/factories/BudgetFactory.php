<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Budget> */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
