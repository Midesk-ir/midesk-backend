<?php

use App\Jobs\AutoCheckout;
use App\Jobs\CalculateDailyAttendance;
use App\Jobs\ContractExpiryReminder;
use App\Jobs\GenerateYearlyLeaveBalances;
use App\Jobs\LeaveBalanceReset;
use App\Jobs\OnboardingStepReminder;
use App\Jobs\SprintDeadlineReminder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SprintDeadlineReminder)->dailyAt('08:00');
Schedule::job(new LeaveBalanceReset)->yearlyOn(1, 1, '00:00');
Schedule::job(new AutoCheckout)->dailyAt('23:30');
Schedule::job(new CalculateDailyAttendance)->dailyAt('23:45');
Schedule::job(new ContractExpiryReminder)->dailyAt('09:00');
Schedule::job(new GenerateYearlyLeaveBalances)->yearlyOn(1, 1, '00:30');
Schedule::job(new OnboardingStepReminder)->dailyAt('09:30');
Schedule::job(new \App\Jobs\OverdueInvoiceChecker)->dailyAt('08:00');
Schedule::job(new \App\Jobs\CalculateAutoScores)->monthlyOn(1, '02:00');
Schedule::job(new \App\Jobs\ReviewCycleReminder)->weeklyOn(1, '09:00');
