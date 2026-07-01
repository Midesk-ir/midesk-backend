<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferenceService
{
    public const EVENT_TYPES = [
        'task.assigned', 'task.mentioned', 'task.due_soon', 'task.overdue',
        'sprint.activated', 'sprint.closed',
        'leave.requested', 'leave.approved', 'leave.rejected',
        'expense.submitted', 'expense.approved', 'expense.rejected', 'expense.paid',
        'attendance.reminder', 'contract.expiring',
        'meeting.invited', 'meeting.starting_soon',
        'review.started', 'review.submitted',
        'chat.mentioned', 'system.announcement',
    ];

    public function listForUser(User $user)
    {
        return NotificationPreference::query()
            ->where('user_id', $user->id)
            ->get();
    }

    public function upsert(User $user, string $eventType, array $channels): NotificationPreference
    {
        return NotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'event_type' => $eventType],
            ['channels' => $channels]
        );
    }

    public function getPreferredChannels(User $user, string $eventType): array
    {
        $pref = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('event_type', $eventType)
            ->first();

        if (! $pref) {
            return ['database', 'mail'];
        }

        $channels = [];
        foreach ($pref->channels as $channel => $enabled) {
            if ($enabled) {
                $channels[] = $channel === 'in_app' ? 'database' : $channel;
            }
        }

        return $channels ?: ['database'];
    }
}
