<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return app(ProjectPolicy::class)->view($user, $project);
    }

    public function view(User $user, Sprint $sprint): bool
    {
        return app(ProjectPolicy::class)->view($user, $sprint->project);
    }

    public function create(User $user, Project $project): bool
    {
        return app(ProjectPolicy::class)->update($user, $project);
    }

    public function update(User $user, Sprint $sprint): bool
    {
        return app(ProjectPolicy::class)->update($user, $sprint->project);
    }

    public function delete(User $user, Sprint $sprint): bool
    {
        return app(ProjectPolicy::class)->update($user, $sprint->project);
    }
}
