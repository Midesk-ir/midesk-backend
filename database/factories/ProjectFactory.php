<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->words(3, true),
            'key' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->sentence(),
            'status' => ProjectStatus::Active,
            'owner_user_id' => User::factory(),
            'start_date' => now()->toDateString(),
            'due_date' => now()->addMonths(3)->toDateString(),
        ];
    }
}
