<?php

namespace Database\Factories;

use App\Models\Shift;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Shift> */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Morning',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'days_of_week' => [1, 2, 3, 4, 5],
            'is_default' => true,
        ];
    }
}
