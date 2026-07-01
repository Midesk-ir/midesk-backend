<?php

namespace App\Enums;

enum ProjectMemberRole: string
{
    case Lead = 'lead';
    case Member = 'member';
    case Viewer = 'viewer';
}
