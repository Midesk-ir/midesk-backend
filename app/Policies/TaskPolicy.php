<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function view(User $user, Task $task): bool
    {
        return $this->isProjectMember($user, $task->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->isProjectMember($user, $project);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->isProjectMember($user, $task->project);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->isProjectMember($user, $task->project);
    }

    private function isProjectMember(User $user, Project $project): bool
    {
        return $project->members()->where('users.id', $user->id)->exists()
            || $project->owner_user_id === $user->id;
    }
}
