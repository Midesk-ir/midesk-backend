<?php

namespace App\Enums;

enum SurveyStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
}
