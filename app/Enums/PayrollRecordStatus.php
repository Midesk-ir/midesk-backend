<?php

namespace App\Enums;

enum PayrollRecordStatus: string
{
    case Draft = 'draft';
    case Calculated = 'calculated';
    case Approved = 'approved';
    case Paid = 'paid';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
