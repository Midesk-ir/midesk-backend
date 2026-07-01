<?php

namespace App\Enums;

enum CrmContactType: string
{
    case Customer = 'customer';
    case Lead = 'lead';
    case Partner = 'partner';
    case Vendor = 'vendor';
}
