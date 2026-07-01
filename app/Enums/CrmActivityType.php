<?php

namespace App\Enums;

enum CrmActivityType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Note = 'note';
    case Task = 'task';
}
