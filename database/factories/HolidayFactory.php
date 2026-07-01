<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Holiday> */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'title' => fake()->words(2, true),
            'date' => now()->addMonths(fake()->numberBetween(1, 6))->toDateString(),
            'recurring_yearly' => fake()->boolean(30),
        ];
    }
}
