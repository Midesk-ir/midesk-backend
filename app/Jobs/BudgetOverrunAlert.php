<?php

namespace App\Jobs;

use App\Models\Budget;
use App\Notifications\BudgetOverrunNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class BudgetOverrunAlert implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Budget $budget,
    ) {}

    public function handle(): void
    {
        app()->instance('tenant', $this->budget->tenant);

        $utilization = $this->budget->amount > 0
            ? ((float) $this->budget->spent / (float) $this->budget->amount) * 100
            : 0;

        if ($utilization < config('finance.budget_alert_percent', 80)) {
            return;
        }

        $financeUsers = $this->budget->tenant->memberships()
            ->whereIn('role', ['OWNER', 'ADMIN', 'FINANCE'])
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter();

        Notification::send($financeUsers, new BudgetOverrunNotification($this->budget, $utilization));

        app()->forgetInstance('tenant');
    }
}
