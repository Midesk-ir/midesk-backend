<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Draft = 'draft';
    case SelfAssessment = 'self_assessment';
    case ManagerReview = 'manager_review';
    case Calibration = 'calibration';
    case Finalized = 'finalized';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
