<?php

namespace Database\Factories;

use App\Models\PerformanceReview;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PerformanceReview> */
class PerformanceReviewFactory extends Factory
{
    protected $model = PerformanceReview::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
        ];
    }
}
