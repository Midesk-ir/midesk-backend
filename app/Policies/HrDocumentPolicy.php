<?php

namespace App\Policies;

use App\Models\HrDocument;
use App\Models\User;
use App\Policies\Concerns\AuthorizesHrAccess;

class HrDocumentPolicy
{
    use AuthorizesHrAccess;

    public function viewAny(User $user): bool
    {
        return $this->membership() !== null;
    }

    public function view(User $user, HrDocument $document): bool
    {
        if ($this->canManageHr()) {
            return true;
        }

        if ($document->user_id === $user->id) {
            return true;
        }

        if ($this->membership()?->role->value === 'MANAGER') {
            return $this->isTeamMember($user, $document->user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->canManageHr();
    }

    public function delete(User $user, HrDocument $document): bool
    {
        return $this->canManageHr();
    }
}
