<?php

namespace Database\Factories;

use App\Models\PerformanceIndicator;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PerformanceIndicator> */
class PerformanceIndicatorFactory extends Factory
{
    protected $model = PerformanceIndicator::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
