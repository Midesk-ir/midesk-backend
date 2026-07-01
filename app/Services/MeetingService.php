<?php

namespace App\Services;

use App\Enums\MeetingAttendeeStatus;
use App\Enums\MeetingProvider;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\User;
use App\Services\Meeting\BbbProvider;
use App\Services\Meeting\GoogleMeetProvider;
use App\Services\Meeting\JitsiProvider;
use App\Services\Meeting\MeetingRoomProvider;
use Illuminate\Support\Str;

class MeetingService
{
    public function roomProvider(?MeetingProvider $provider = null): MeetingRoomProvider
    {
        $name = $provider?->value ?? config('meeting.provider', 'jitsi');

        return match ($name) {
            'bbb' => new BbbProvider,
            'google_meet' => new GoogleMeetProvider,
            default => new JitsiProvider,
        };
    }

    public function create(User $user, array $data): Meeting
    {
        $meeting = Meeting::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'organizer_user_id' => $user->id,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'provider' => MeetingProvider::from($data['provider'] ?? 'jitsi'),
            'status' => MeetingStatus::Scheduled,
        ]);

        $attendeeIds = array_unique(array_merge([$user->id], $data['attendee_ids'] ?? []));
        foreach ($attendeeIds as $attendeeId) {
            MeetingAttendee::create([
                'meeting_id' => $meeting->id,
                'user_id' => $attendeeId,
                'status' => $attendeeId === $user->id
                    ? MeetingAttendeeStatus::Accepted
                    : MeetingAttendeeStatus::Invited,
            ]);
        }

        return $meeting->load(['attendees.user', 'organizer']);
    }

    public function start(Meeting $meeting, User $user): Meeting
    {
        $roomId = $meeting->room_id ?? Str::slug($meeting->title).'-'.Str::substr($meeting->id, 0, 8);
        $provider = $this->roomProvider($meeting->provider);
        $roomUrl = $provider->createRoom($roomId, $meeting->title);
        $joinUrl = $provider->joinUrl($roomId, $user->full_name, true);

        $meeting->update([
            'room_id' => $roomId,
            'room_url' => $joinUrl ?: $roomUrl,
            'status' => MeetingStatus::InProgress,
        ]);

        return $meeting->fresh()->load(['attendees.user', 'organizer']);
    }

    public function join(Meeting $meeting, User $user): array
    {
        $attendee = MeetingAttendee::query()
            ->where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $attendee->update([
            'status' => MeetingAttendeeStatus::Attended,
            'joined_at' => now(),
        ]);

        $provider = $this->roomProvider($meeting->provider);
        $roomId = $meeting->room_id ?? Str::slug($meeting->title);

        return [
            'room_url' => $meeting->room_url ?? $provider->joinUrl($roomId, $user->full_name),
            'meeting' => $meeting->fresh()->load(['attendees.user']),
        ];
    }

    public function end(Meeting $meeting): Meeting
    {
        $meeting->update(['status' => MeetingStatus::Completed]);

        return $meeting->fresh();
    }

    public function respond(Meeting $meeting, User $user, MeetingAttendeeStatus $status): MeetingAttendee
    {
        $attendee = MeetingAttendee::query()
            ->where('meeting_id', $meeting->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $attendee->update(['status' => $status]);

        return $attendee->fresh();
    }
}
