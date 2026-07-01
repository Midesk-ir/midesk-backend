<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CalendarEvent> */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 30));

        return [
            'tenant_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'start_at' => $start,
            'end_at' => $start->copy()->addHours(2),
            'all_day' => false,
            'color' => fake()->hexColor(),
            'recurrence_rule' => null,
            'created_by_user_id' => User::factory(),
            'created_at' => now(),
        ];
    }
}
