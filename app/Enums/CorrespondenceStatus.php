<?php

namespace App\Enums;

enum CorrespondenceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Received = 'received';
    case Archived = 'archived';
}
