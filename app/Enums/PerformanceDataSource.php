<?php

namespace App\Enums;

enum PerformanceDataSource: string
{
    case Manual = 'manual';
    case AutoTasks = 'auto_tasks';
    case AutoAttendance = 'auto_attendance';
    case AutoWorklog = 'auto_worklog';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
