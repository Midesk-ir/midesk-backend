<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Terminated = 'terminated';
    case Renewed = 'renewed';
}
