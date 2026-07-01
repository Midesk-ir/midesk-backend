<?php

namespace App\Policies;

use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    public function viewAny(User $user): bool
    {
        return app('membership') !== null;
    }

    public function create(User $user): bool
    {
        return app('membership') !== null;
    }

    public function update(User $user, CalendarEvent $event): bool
    {
        return $event->created_by_user_id === $user->id
            || app('membership')?->isOwnerOrAdmin();
    }

    public function delete(User $user, CalendarEvent $event): bool
    {
        return $this->update($user, $event);
    }
}
