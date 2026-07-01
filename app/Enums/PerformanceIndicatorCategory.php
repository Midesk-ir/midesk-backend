<?php

namespace App\Enums;

enum PerformanceIndicatorCategory: string
{
    case Quality = 'quality';
    case Efficiency = 'efficiency';
    case Timeliness = 'timeliness';
    case Teamwork = 'teamwork';
    case Custom = 'custom';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
