<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\OnboardingStepReminderNotification;
use App\Services\OnboardingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class OnboardingStepReminder implements ShouldQueue
{
    use Queueable;

    public function handle(OnboardingService $onboardingService): void
    {
        foreach ($onboardingService->overdueSteps() as $progress) {
            $steps = $progress->template->steps ?? [];
            $step = $steps[$progress->current_step] ?? null;

            if (! $step) {
                continue;
            }

            $assigneeRole = $step['assignee_role'] ?? 'hr';
            $users = User::query()
                ->whereHas('memberships', fn ($q) => $q
                    ->where('tenant_id', $progress->tenant_id)
                    ->when($assigneeRole === 'hr', fn ($q) => $q->whereIn('role', ['HR_MANAGER', 'ADMIN', 'OWNER'])))
                ->get();

            foreach ($users as $user) {
                $user->notify(new OnboardingStepReminderNotification($progress, $step));
            }
        }
    }
}
