<?php

namespace App\Enums;

enum InterviewType: string
{
    case Phone = 'phone';
    case Video = 'video';
    case InPerson = 'in_person';
    case Technical = 'technical';
}
