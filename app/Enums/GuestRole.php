<?php

namespace App\Enums;

enum GuestRole: string
{
    case Viewer = 'viewer';
    case Commenter = 'commenter';
}
