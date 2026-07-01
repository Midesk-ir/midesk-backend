<?php

namespace App\Jobs;

use App\Services\LeavePolicyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateYearlyLeaveBalances implements ShouldQueue
{
    use Queueable;

    public function handle(LeavePolicyService $leavePolicyService): void
    {
        $leavePolicyService->generateYearlyBalances((int) now()->year);
    }
}
