<?php

namespace App\Services\Meeting;

interface MeetingRoomProvider
{
    public function createRoom(string $roomId, string $title, array $options = []): string;

    public function joinUrl(string $roomId, string $userName, bool $moderator = false): string;
}
