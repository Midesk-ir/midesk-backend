<?php

namespace App\Enums;

enum PersonnelStatus: string
{
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Terminated = 'terminated';
}
