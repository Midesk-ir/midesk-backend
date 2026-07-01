<?php

namespace App\Policies\Concerns;

use App\Models\Membership;
use App\Models\Team;
use App\Models\User;

trait AuthorizesHrAccess
{
    protected function membership(): ?Membership
    {
        return app('membership');
    }

    protected function canManageHr(): bool
    {
        return $this->membership()?->canManageHr() ?? false;
    }

    protected function isOwnerOrAdmin(): bool
    {
        return $this->membership()?->isOwnerOrAdmin() ?? false;
    }

    protected function isManagerOrAbove(): bool
    {
        return $this->membership()?->isManagerOrAbove() ?? false;
    }

    protected function isSameUser(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    protected function isTeamMember(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return true;
        }

        $teamIds = Team::query()
            ->where('lead_user_id', $user->id)
            ->pluck('id');

        if ($teamIds->isEmpty()) {
            return false;
        }

        return Team::query()
            ->whereIn('id', $teamIds)
            ->whereHas('members', fn ($q) => $q->where('users.id', $target->id))
            ->exists();
    }
}
