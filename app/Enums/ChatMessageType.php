<?php

namespace App\Enums;

enum ChatMessageType: string
{
    case Text = 'text';
    case File = 'file';
    case Image = 'image';
    case System = 'system';
}
