<?php

namespace App\Services\Meeting;

class GoogleMeetProvider implements MeetingRoomProvider
{
    public function createRoom(string $roomId, string $title, array $options = []): string
    {
        return 'https://meet.google.com/new';
    }

    public function joinUrl(string $roomId, string $userName, bool $moderator = false): string
    {
        return 'https://meet.google.com/new';
    }
}
