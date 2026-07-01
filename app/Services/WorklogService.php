<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Worklog;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WorklogService
{
    private const MAX_DAILY_MINUTES = 1440;

    public function create(User $user, array $data): Worklog
    {
        $project = Project::findOrFail($data['project_id']);
        $this->assertProjectNotArchived($project);
        $this->assertDailyLimit($user->id, $data['logged_date'], (int) $data['duration_minutes']);

        if (! empty($data['task_id'])) {
            $task = Task::findOrFail($data['task_id']);
            if ($task->project_id !== $project->id) {
                throw ValidationException::withMessages([
                    'task_id' => [__('errors.worklog_task_project_mismatch')],
                ]);
            }
        }

        return Worklog::create([
            'tenant_id' => app('tenant')->id,
            'user_id' => $user->id,
            'task_id' => $data['task_id'] ?? null,
            'project_id' => $project->id,
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'],
            'logged_date' => $data['logged_date'],
        ]);
    }

    public function update(Worklog $worklog, array $data): Worklog
    {
        $project = Project::findOrFail($data['project_id'] ?? $worklog->project_id);
        $this->assertProjectNotArchived($project);

        $duration = (int) ($data['duration_minutes'] ?? $worklog->duration_minutes);
        $loggedDate = $data['logged_date'] ?? $worklog->logged_date->toDateString();

        $this->assertDailyLimit(
            $worklog->user_id,
            $loggedDate,
            $duration,
            $worklog->id
        );

        $worklog->update($data);

        return $worklog->fresh();
    }

    public function delete(Worklog $worklog): void
    {
        $worklog->delete();
    }

    /** @return Collection<int, Worklog> */
    public function myWorklogs(User $user, array $filters = []): Collection
    {
        $query = Worklog::query()
            ->where('user_id', $user->id)
            ->with(['project', 'task']);

        $this->applyFilters($query, $filters);

        return $query->orderByDesc('logged_date')->get();
    }

    /** @return Collection<int, Worklog> */
    public function teamWorklogs(array $userIds, array $filters = []): Collection
    {
        $query = Worklog::query()
            ->whereIn('user_id', $userIds)
            ->with(['project', 'task', 'user']);

        $this->applyFilters($query, $filters);

        return $query->orderByDesc('logged_date')->get();
    }

    /** @return array<string, mixed> */
    public function aggregateReport(array $userIds, array $filters = []): array
    {
        $query = Worklog::query()->whereIn('user_id', $userIds);
        $this->applyFilters($query, $filters);

        $worklogs = $query->with(['user', 'project'])->get();

        return [
            'by_user' => $worklogs->groupBy('user_id')->map(fn ($items, $userId) => [
                'user_id' => $userId,
                'user_name' => $items->first()->user?->full_name,
                'total_minutes' => $items->sum('duration_minutes'),
            ])->values(),
            'by_project' => $worklogs->groupBy('project_id')->map(fn ($items, $projectId) => [
                'project_id' => $projectId,
                'project_name' => $items->first()->project?->name,
                'total_minutes' => $items->sum('duration_minutes'),
            ])->values(),
            'by_day' => $worklogs->groupBy(fn ($w) => $w->logged_date->toDateString())->map(fn ($items, $date) => [
                'date' => $date,
                'total_minutes' => $items->sum('duration_minutes'),
            ])->values(),
        ];
    }

    public function weekMinutesForUser(User $user): int
    {
        return (int) Worklog::query()
            ->where('user_id', $user->id)
            ->whereBetween('logged_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ])
            ->sum('duration_minutes');
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['from_date'])) {
            $query->where('logged_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->where('logged_date', '<=', $filters['to_date']);
        }

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
    }

    private function assertProjectNotArchived(Project $project): void
    {
        if ($project->status === ProjectStatus::Archived) {
            throw ValidationException::withMessages([
                'project_id' => [__('errors.worklog_archived_project')],
            ]);
        }
    }

    private function assertDailyLimit(string $userId, string $date, int $minutes, ?string $excludeId = null): void
    {
        $query = Worklog::query()
            ->where('user_id', $userId)
            ->whereDate('logged_date', $date);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $existing = (int) $query->sum('duration_minutes');

        if ($existing + $minutes > self::MAX_DAILY_MINUTES) {
            throw ValidationException::withMessages([
                'duration_minutes' => [__('errors.worklog_daily_limit_exceeded')],
            ]);
        }
    }
}
