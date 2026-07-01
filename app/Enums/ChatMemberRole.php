<?php

namespace App\Enums;

enum ChatMemberRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';
}
