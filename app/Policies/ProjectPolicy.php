<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Enums\ProjectMemberRole;
use App\Models\Membership;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->activeMembership() !== null;
    }

    public function view(User $user, Project $project): bool
    {
        return $this->isProjectMember($user, $project) || $this->isOwnerOrAdmin();
    }

    public function create(User $user): bool
    {
        return $this->activeMembership() !== null;
    }

    public function update(User $user, Project $project): bool
    {
        if ($this->isOwnerOrAdmin()) {
            return true;
        }

        return $project->owner_user_id === $user->id
            || $this->hasProjectRole($user, $project, ProjectMemberRole::Lead);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->isOwnerOrAdmin() || $project->owner_user_id === $user->id;
    }

    public function manageMembers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    private function activeMembership(): ?Membership
    {
        return app('membership');
    }

    private function isOwnerOrAdmin(): bool
    {
        return $this->activeMembership()?->isOwnerOrAdmin() ?? false;
    }

    private function isProjectMember(User $user, Project $project): bool
    {
        return $project->members()->where('users.id', $user->id)->exists()
            || $project->owner_user_id === $user->id;
    }

    private function hasProjectRole(User $user, Project $project, ProjectMemberRole $role): bool
    {
        return $project->members()
            ->where('users.id', $user->id)
            ->wherePivot('role', $role->value)
            ->exists();
    }
}
