<?php

namespace App\Enums;

enum NoteVisibility: string
{
    case Private = 'private';
    case Team = 'team';
    case Project = 'project';
}
