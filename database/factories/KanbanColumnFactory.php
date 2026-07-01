<?php

namespace Database\Factories;

use App\Models\KanbanColumn;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<KanbanColumn> */
class KanbanColumnFactory extends Factory
{
    protected $model = KanbanColumn::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);

        return [
            'tenant_id' => null,
            'project_id' => Project::factory(),
            'name' => ucfirst(str_replace('_', ' ', $slug)),
            'slug' => $slug,
            'color' => fake()->hexColor(),
            'order_index' => fake()->randomFloat(4, 1024, 8192),
            'wip_limit' => null,
            'is_done_column' => false,
            'created_at' => now(),
        ];
    }
}
