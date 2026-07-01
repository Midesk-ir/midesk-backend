<?php

namespace App\Enums;

enum ChatChannelType: string
{
    case Dm = 'dm';
    case Group = 'group';
    case Channel = 'channel';
}
