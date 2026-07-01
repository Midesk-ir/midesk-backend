<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case New = 'new';
    case Screening = 'screening';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';
}
