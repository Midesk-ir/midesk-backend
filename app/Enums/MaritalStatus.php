<?php

namespace App\Enums;

enum MaritalStatus: string
{
    case Single = 'single';
    case Married = 'married';
    case Other = 'other';
}
