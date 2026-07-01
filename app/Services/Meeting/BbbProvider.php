<?php

namespace App\Services\Meeting;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BbbProvider implements MeetingRoomProvider
{
    public function createRoom(string $roomId, string $title, array $options = []): string
    {
        $url = rtrim(config('meeting.bbb_url'), '/');
        $secret = config('meeting.bbb_secret');

        if (! $url || ! $secret) {
            return "https://bbb.example.com/join/{$roomId}";
        }

        $query = http_build_query([
            'name' => $title,
            'meetingID' => $roomId,
            'attendeePW' => Str::random(8),
            'moderatorPW' => Str::random(8),
        ]);
        $checksum = sha1('create'.$query.$secret);

        Http::get("{$url}/api/create?{$query}&checksum={$checksum}");

        return "{$url}/{$roomId}";
    }

    public function joinUrl(string $roomId, string $userName, bool $moderator = false): string
    {
        $url = rtrim(config('meeting.bbb_url'), '/');
        $secret = config('meeting.bbb_secret');

        if (! $url || ! $secret) {
            return "https://bbb.example.com/join/{$roomId}?name=".urlencode($userName);
        }

        $password = $moderator ? 'modpass' : 'attpass';
        $query = http_build_query([
            'fullName' => $userName,
            'meetingID' => $roomId,
            'password' => $password,
        ]);
        $checksum = sha1('join'.$query.$secret);

        return "{$url}/api/join?{$query}&checksum={$checksum}";
    }
}
