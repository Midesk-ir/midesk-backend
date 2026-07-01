<?php

namespace App\Jobs;

use App\Models\CustomReport;
use App\Services\ReportExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledReports implements ShouldQueue
{
    use Queueable;

    public function handle(ReportExportService $exportService): void
    {
        CustomReport::query()
            ->whereNotNull('schedule')
            ->each(function (CustomReport $report) use ($exportService): void {
                $schedule = $report->schedule ?? [];
                $recipients = $schedule['recipients'] ?? [];
                if (empty($recipients)) {
                    return;
                }

                $export = $exportService->export($report, 'csv');
                foreach ($recipients as $email) {
                    Log::info('Scheduled report sent', ['report' => $report->id, 'email' => $email, 'file' => $export['path']]);
                }
            });
    }
}
