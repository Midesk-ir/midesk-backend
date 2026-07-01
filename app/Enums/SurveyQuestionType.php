<?php

namespace App\Enums;

enum SurveyQuestionType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case Rating = 'rating';
    case Number = 'number';
    case Date = 'date';
}
