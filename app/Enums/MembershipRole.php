<?php

namespace App\Enums;

enum MembershipRole: string
{
    case Owner = 'OWNER';
    case Admin = 'ADMIN';
    case Manager = 'MANAGER';
    case HrManager = 'HR_MANAGER';
    case Finance = 'FINANCE';
    case Member = 'MEMBER';
}
