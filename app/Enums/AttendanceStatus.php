<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';
    case HalfDay = 'half_day';
    case OnLeave = 'on_leave';
    case Mission = 'mission';
    case Holiday = 'holiday';
}
