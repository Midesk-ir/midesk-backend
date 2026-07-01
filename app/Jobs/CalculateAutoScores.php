<?php

namespace App\Jobs;

use App\Enums\PerformanceDataSource;
use App\Models\Membership;
use App\Models\PerformanceIndicator;
use App\Models\Tenant;
use App\Services\PerformanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateAutoScores implements ShouldQueue
{
    use Queueable;

    public function handle(PerformanceService $performanceService): void
    {
        $period = now()->format('Y-m');
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            app()->instance('tenant', $tenant);

            $indicators = PerformanceIndicator::query()
                ->where('is_active', true)
                ->where('data_source', '!=', PerformanceDataSource::Manual->value)
                ->get();

            $userIds = Membership::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->pluck('user_id');

            foreach ($indicators as $indicator) {
                foreach ($userIds as $userId) {
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        $performanceService->calculateAutoScore($indicator, $user, $period);
                    }
                }
            }

            app()->forgetInstance('tenant');
        }
    }
}
