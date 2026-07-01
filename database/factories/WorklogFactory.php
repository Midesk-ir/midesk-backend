<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Worklog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Worklog> */
class WorklogFactory extends Factory
{
    protected $model = Worklog::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'user_id' => User::factory(),
            'task_id' => null,
            'project_id' => Project::factory(),
            'description' => fake()->optional()->sentence(),
            'duration_minutes' => fake()->numberBetween(30, 480),
            'logged_date' => now()->toDateString(),
            'created_at' => now(),
        ];
    }
}
