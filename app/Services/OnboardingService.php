<?php

namespace App\Services;

use App\Enums\OnboardingProgressStatus;
use App\Models\OnboardingProgress;
use App\Models\OnboardingTemplate;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class OnboardingService
{
    public function listTemplates(): LengthAwarePaginator
    {
        return OnboardingTemplate::query()->orderByDesc('is_default')->paginate();
    }

    public function createTemplate(array $data): OnboardingTemplate
    {
        if (! empty($data['is_default'])) {
            OnboardingTemplate::query()->update(['is_default' => false]);
        }

        return OnboardingTemplate::create($data);
    }

    public function updateTemplate(OnboardingTemplate $template, array $data): OnboardingTemplate
    {
        if (! empty($data['is_default'])) {
            OnboardingTemplate::query()
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->update($data);

        return $template->fresh();
    }

    public function defaultTemplate(): ?OnboardingTemplate
    {
        return OnboardingTemplate::query()
            ->where('is_default', true)
            ->first()
            ?? OnboardingTemplate::query()->first();
    }

    public function listActiveProgress(): LengthAwarePaginator
    {
        return OnboardingProgress::query()
            ->where('status', OnboardingProgressStatus::InProgress)
            ->with(['user', 'template'])
            ->paginate();
    }

    public function progressForUser(User $user): ?OnboardingProgress
    {
        return OnboardingProgress::query()
            ->where('user_id', $user->id)
            ->with('template')
            ->latest('started_at')
            ->first();
    }

    public function startForUser(User $user, ?string $templateId = null): OnboardingProgress
    {
        $existing = OnboardingProgress::query()
            ->where('user_id', $user->id)
            ->where('status', OnboardingProgressStatus::InProgress)
            ->first();

        if ($existing) {
            return $existing;
        }

        $template = $templateId
            ? OnboardingTemplate::findOrFail($templateId)
            : $this->defaultTemplate();

        if (! $template) {
            throw ValidationException::withMessages([
                'template' => [__('errors.no_onboarding_template')],
            ]);
        }

        return OnboardingProgress::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'current_step' => 0,
            'status' => OnboardingProgressStatus::InProgress,
            'started_at' => now(),
            'step_completions' => [],
        ]);
    }

    public function completeStep(User $user, int $stepIndex, User $completedBy): OnboardingProgress
    {
        $progress = OnboardingProgress::query()
            ->where('user_id', $user->id)
            ->where('status', OnboardingProgressStatus::InProgress)
            ->firstOrFail();

        $template = $progress->template;
        $steps = $template->steps ?? [];

        if ($stepIndex >= count($steps)) {
            throw ValidationException::withMessages([
                'step_index' => [__('errors.invalid_onboarding_step')],
            ]);
        }

        $completions = $progress->step_completions ?? [];
        $completions[] = [
            'step' => $stepIndex,
            'completed_at' => now()->toIso8601String(),
            'completed_by' => $completedBy->id,
        ];

        $nextStep = $stepIndex + 1;
        $isComplete = $nextStep >= count($steps);

        $progress->update([
            'step_completions' => $completions,
            'current_step' => $isComplete ? $stepIndex : $nextStep,
            'status' => $isComplete ? OnboardingProgressStatus::Completed : OnboardingProgressStatus::InProgress,
            'completed_at' => $isComplete ? now() : null,
        ]);

        return $progress->fresh()->load('template');
    }

    /** @return Collection<int, OnboardingProgress> */
    public function overdueSteps(): Collection
    {
        return OnboardingProgress::query()
            ->where('status', OnboardingProgressStatus::InProgress)
            ->with(['user', 'template'])
            ->get()
            ->filter(function (OnboardingProgress $progress) {
                $steps = $progress->template->steps ?? [];
                $currentStep = $progress->current_step;

                if (! isset($steps[$currentStep])) {
                    return false;
                }

                $dueDays = $steps[$currentStep]['due_days'] ?? 7;
                $dueDate = $progress->started_at->copy()->addDays($dueDays);

                return now()->gt($dueDate);
            });
    }
}
