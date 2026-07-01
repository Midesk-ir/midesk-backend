<?php

namespace App\Enums;

enum HrDocumentCategory: string
{
    case NationalId = 'national_id';
    case Certificate = 'certificate';
    case Resume = 'resume';
    case Contract = 'contract';
    case Other = 'other';
}
