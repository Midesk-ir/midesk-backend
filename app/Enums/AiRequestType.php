<?php

namespace App\Enums;

enum AiRequestType: string
{
    case TaskAnalysis = 'task_analysis';
    case DocumentGeneration = 'document_generation';
    case ResumeScreening = 'resume_screening';
    case PerformanceAnalysis = 'performance_analysis';
    case GeneralQuery = 'general_query';
}
