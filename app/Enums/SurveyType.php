<?php

namespace App\Enums;

enum SurveyType: string
{
    case Survey = 'survey';
    case Quiz = 'quiz';
    case Form = 'form';
}
