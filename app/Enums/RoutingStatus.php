<?php

namespace App\Enums;

enum RoutingStatus: string
{
    case Pending = 'pending';
    case Seen = 'seen';
    case Completed = 'completed';
}
