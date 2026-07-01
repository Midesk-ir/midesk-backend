<?php

namespace App\Jobs;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MeetingReminder implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $windowStart = now();
        $windowEnd = now()->addMinutes(10);

        Meeting::query()
            ->where('status', MeetingStatus::Scheduled)
            ->whereBetween('start_at', [$windowStart, $windowEnd])
            ->with('attendees.user')
            ->each(function (Meeting $meeting): void {
                Log::info('Meeting reminder', ['meeting_id' => $meeting->id, 'title' => $meeting->title]);
            });
    }
}
