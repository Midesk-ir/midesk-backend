<?php

namespace App\Enums;

enum CorrespondenceType: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
    case Internal = 'internal';
}
