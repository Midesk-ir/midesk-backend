<?php

namespace App\Enums;

enum MeetingProvider: string
{
    case Jitsi = 'jitsi';
    case Bbb = 'bbb';
    case GoogleMeet = 'google_meet';
}
