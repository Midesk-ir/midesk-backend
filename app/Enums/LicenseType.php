<?php

namespace App\Enums;

enum LicenseType: string
{
    case Trial = 'trial';
    case Standard = 'standard';
    case Enterprise = 'enterprise';
}
