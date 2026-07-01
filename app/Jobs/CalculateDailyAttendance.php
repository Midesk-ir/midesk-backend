<?php

namespace App\Jobs;

use App\Services\AttendanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateDailyAttendance implements ShouldQueue
{
    use Queueable;

    public function handle(AttendanceService $attendanceService): void
    {
        $attendanceService->calculateDailyAttendance();
    }
}
