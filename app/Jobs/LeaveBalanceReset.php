<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\LeaveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LeaveBalanceReset implements ShouldQueue
{
    use Queueable;

    public function handle(LeaveService $leaveService): void
    {
        $year = (int) now()->year;

        Tenant::query()->each(function (Tenant $tenant) use ($leaveService, $year): void {
            app()->instance('tenant', $tenant);
            $leaveService->resetBalancesForYear($year);
            app()->forgetInstance('tenant');
        });
    }
}
