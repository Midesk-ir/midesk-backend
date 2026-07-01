<?php

namespace App\Enums;

enum RequisitionStatus: string
{
    case Open = 'open';
    case Filled = 'filled';
    case Cancelled = 'cancelled';
}
