<?php

namespace App\Services\Meeting;

use Illuminate\Support\Str;

class JitsiProvider implements MeetingRoomProvider
{
    public function createRoom(string $roomId, string $title, array $options = []): string
    {
        $domain = config('meeting.jitsi_domain', 'meet.jit.si');

        return "https://{$domain}/{$roomId}";
    }

    public function joinUrl(string $roomId, string $userName, bool $moderator = false): string
    {
        $domain = config('meeting.jitsi_domain', 'meet.jit.si');
        $url = "https://{$domain}/{$roomId}";

        $secret = config('meeting.jitsi_secret');
        $appId = config('meeting.jitsi_app_id');

        if ($secret && $appId) {
            $payload = [
                'context' => ['user' => ['name' => $userName, 'moderator' => $moderator]],
                'room' => $roomId,
                'exp' => now()->addHours(3)->timestamp,
            ];
            $token = $this->encodeJwt($payload, $secret, $appId);

            return "{$url}?jwt={$token}";
        }

        return $url.'#userInfo.displayName="'.urlencode($userName).'"';
    }

    private function encodeJwt(array $payload, string $secret, string $appId): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iss'] = $appId;
        $payload['aud'] = 'jitsi';
        $payload['sub'] = config('meeting.jitsi_domain');
        $body = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac('sha256', "{$header}.{$body}", $secret, true));

        return strtr("{$header}.{$body}.{$signature}", '+/', '-_');
    }
}
