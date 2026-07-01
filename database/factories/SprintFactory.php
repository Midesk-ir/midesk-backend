<?php

namespace Database\Factories;

use App\Enums\SprintStatus;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sprint> */
class SprintFactory extends Factory
{
    protected $model = Sprint::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $start = now()->startOfWeek();

        return [
            'tenant_id' => null,
            'project_id' => Project::factory(),
            'name' => 'Sprint '.fake()->numberBetween(1, 20),
            'goal' => fake()->optional()->sentence(),
            'status' => SprintStatus::Planning,
            'start_date' => $start,
            'end_date' => $start->copy()->addWeeks(2)->subDay(),
        ];
    }
}
