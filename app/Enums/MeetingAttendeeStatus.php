<?php

namespace App\Enums;

enum MeetingAttendeeStatus: string
{
    case Invited = 'invited';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Attended = 'attended';
}
