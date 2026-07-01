<?php

namespace App\Enums;

enum ContractType: string
{
    case Permanent = 'permanent';
    case FixedTerm = 'fixed_term';
    case Trial = 'trial';
    case Intern = 'intern';
}
