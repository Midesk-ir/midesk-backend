<?php

namespace App\Services;

use App\Enums\ObjectiveStatus;
use App\Models\KeyResult;
use App\Models\Objective;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OkrService
{
    /** @return Collection<int, Objective> */
    public function list(array $filters = []): Collection
    {
        $query = Objective::query()->with(['keyResults', 'user']);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function create(User $user, array $data): Objective
    {
        return Objective::create([
            'tenant_id' => app('tenant')->id,
            'user_id' => $data['user_id'] ?? $user->id,
            'status' => ObjectiveStatus::Draft,
            'progress_percent' => 0,
            ...$data,
        ]);
    }

    public function update(Objective $objective, array $data): Objective
    {
        $objective->update($data);

        return $objective->fresh(['keyResults', 'user']);
    }

    public function delete(Objective $objective): void
    {
        $objective->keyResults()->delete();
        $objective->delete();
    }

    public function addKeyResult(Objective $objective, array $data): KeyResult
    {
        $keyResult = KeyResult::create([
            'tenant_id' => app('tenant')->id,
            'objective_id' => $objective->id,
            'current_value' => 0,
            ...$data,
        ]);

        $this->recalculateProgress($objective);

        return $keyResult->fresh();
    }

    public function updateKeyResult(KeyResult $keyResult, array $data): KeyResult
    {
        $keyResult->update($data);
        $this->recalculateProgress($keyResult->objective);

        return $keyResult->fresh('objective');
    }

    public function updateProgress(KeyResult $keyResult, float $currentValue): KeyResult
    {
        $keyResult->update(['current_value' => $currentValue]);
        $this->recalculateProgress($keyResult->objective);

        return $keyResult->fresh('objective.keyResults');
    }

    public function recalculateProgress(Objective $objective): void
    {
        $objective->load('keyResults');
        $keyResults = $objective->keyResults;

        if ($keyResults->isEmpty()) {
            $objective->update(['progress_percent' => 0]);

            return;
        }

        $totalWeight = $keyResults->sum(fn (KeyResult $kr) => (float) $kr->weight);
        $progress = $keyResults->sum(function (KeyResult $kr) use ($totalWeight) {
            $target = (float) $kr->target_value;
            $percent = $target > 0 ? min(100, ((float) $kr->current_value / $target) * 100) : 0;

            return $percent * ((float) $kr->weight / max(1, $totalWeight));
        });

        $objective->update(['progress_percent' => (int) round($progress)]);
    }

    public function assertCanManageObjective(User $user, Objective $objective): void
    {
        $membership = app('membership');

        if ($membership?->isOwnerOrAdmin() || $membership?->isManagerOrAbove()) {
            return;
        }

        if ($objective->user_id !== $user->id) {
            throw ValidationException::withMessages(['objective' => [__('errors.forbidden')]]);
        }
    }
}
