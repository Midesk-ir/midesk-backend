<?php

namespace App\Enums;

enum OnboardingProgressStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
