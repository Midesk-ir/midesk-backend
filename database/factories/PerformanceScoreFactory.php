<?php

namespace Database\Factories;

use App\Models\PerformanceScore;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PerformanceScore> */
class PerformanceScoreFactory extends Factory
{
    protected $model = PerformanceScore::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
