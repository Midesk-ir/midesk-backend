<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use App\Services\ContractService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ContractExpiryReminder implements ShouldQueue
{
    use Queueable;

    public function handle(ContractService $contractService): void
    {
        $expiring = $contractService->expiring();

        foreach ($expiring as $contract) {
            $hrUsers = User::query()
                ->whereHas('memberships', fn ($q) => $q
                    ->where('tenant_id', $contract->tenant_id)
                    ->whereIn('role', ['OWNER', 'ADMIN', 'HR_MANAGER']))
                ->get();

            foreach ($hrUsers as $user) {
                $user->notify(new ContractExpiringNotification($contract));
            }
        }
    }
}
