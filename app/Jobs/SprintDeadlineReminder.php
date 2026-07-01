<?php

namespace App\Jobs;

use App\Enums\SprintStatus;
use App\Models\Sprint;
use App\Models\Tenant;
use App\Notifications\SprintDeadlineApproachingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SprintDeadlineReminder implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $targetDate = now()->addDays(2)->toDateString();

        Tenant::query()->each(function (Tenant $tenant): void {
            app()->instance('tenant', $tenant);

            $sprints = Sprint::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('status', SprintStatus::Active)
                ->whereDate('end_date', $targetDate)
                ->with(['project.members', 'project.owner'])
                ->get();

            foreach ($sprints as $sprint) {
                $daysRemaining = now()->diffInDays($sprint->end_date, false);
                $users = collect([$sprint->project->owner])->merge($sprint->project->members)->unique('id');

                foreach ($users as $user) {
                    $user?->notify(new SprintDeadlineApproachingNotification($sprint, (int) $daysRemaining));
                }
            }

            app()->forgetInstance('tenant');
        });
    }
}
