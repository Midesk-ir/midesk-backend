<?php

namespace App\Enums;

enum CandidateSource: string
{
    case Website = 'website';
    case Referral = 'referral';
    case Agency = 'agency';
    case Linkedin = 'linkedin';
    case Other = 'other';
}
