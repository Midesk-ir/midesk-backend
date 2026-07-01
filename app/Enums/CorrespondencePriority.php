<?php

namespace App\Enums;

enum CorrespondencePriority: string
{
    case Normal = 'normal';
    case Urgent = 'urgent';
    case Confidential = 'confidential';
}
