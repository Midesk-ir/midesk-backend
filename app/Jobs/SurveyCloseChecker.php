<?php

namespace App\Jobs;

use App\Enums\SurveyStatus;
use App\Models\Survey;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SurveyCloseChecker implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Survey::query()
            ->where('status', SurveyStatus::Active)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => SurveyStatus::Closed]);
    }
}
