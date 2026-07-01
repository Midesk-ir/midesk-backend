<?php

namespace App\Enums;

enum SprintStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case Review = 'review';
    case Closed = 'closed';
}
