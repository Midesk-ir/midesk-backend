<?php

namespace App\Policies;

use App\Models\KanbanColumn;
use App\Models\Project;
use App\Models\User;

class KanbanColumnPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return app(ProjectPolicy::class)->view($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        return app(ProjectPolicy::class)->update($user, $project);
    }

    public function update(User $user, KanbanColumn $column): bool
    {
        return app(ProjectPolicy::class)->update($user, $column->project);
    }

    public function delete(User $user, KanbanColumn $column): bool
    {
        return app(ProjectPolicy::class)->update($user, $column->project);
    }
}
