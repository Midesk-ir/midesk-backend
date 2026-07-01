<?php

namespace App\Jobs;

use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateLeaveBalances implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly LeaveType $leaveType,
    ) {}

    public function handle(LeaveService $leaveService): void
    {
        app()->instance('tenant', $this->leaveType->tenant);
        $leaveService->generateBalancesForLeaveType($this->leaveType);
        app()->forgetInstance('tenant');
    }
}
