<?php

namespace App\Enums;

enum RoutingAction: string
{
    case Review = 'review';
    case Approve = 'approve';
    case Sign = 'sign';
    case ForInfo = 'for_info';
    case Execute = 'execute';
}
