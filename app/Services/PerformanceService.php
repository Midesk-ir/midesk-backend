<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\PerformanceDataSource;
use App\Enums\TaskStatus;
use App\Models\AttendanceRecord;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceScore;
use App\Models\Task;
use App\Models\User;
use App\Models\Worklog;
use Illuminate\Support\Collection;

class PerformanceService
{
    public function createIndicator(array $data): PerformanceIndicator
    {
        return PerformanceIndicator::create([
            'tenant_id' => app('tenant')->id,
            ...$data,
        ]);
    }

    public function updateIndicator(PerformanceIndicator $indicator, array $data): PerformanceIndicator
    {
        $indicator->update($data);

        return $indicator->fresh();
    }

    public function deleteIndicator(PerformanceIndicator $indicator): void
    {
        $indicator->delete();
    }

    /** @return Collection<int, PerformanceIndicator> */
    public function listIndicators(): Collection
    {
        return PerformanceIndicator::query()->orderBy('name')->get();
    }

    public function recordScore(User $evaluator, array $data): PerformanceScore
    {
        return PerformanceScore::create([
            'tenant_id' => app('tenant')->id,
            'evaluator_user_id' => $evaluator->id,
            ...$data,
        ]);
    }

    /** @return Collection<int, PerformanceScore> */
    public function listScores(array $filters = []): Collection
    {
        $query = PerformanceScore::query()->with(['user', 'indicator', 'evaluator']);

        foreach (['user_id', 'period', 'indicator_id'] as $field) {
            if (isset($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        return $query->orderByDesc('created_at')->get();
    }

    /** @return array{summaries: list<array<string, mixed>>} */
    public function scoresSummary(string $period): array
    {
        $scores = PerformanceScore::query()
            ->with('indicator')
            ->where('period', $period)
            ->get()
            ->groupBy('user_id');

        $summaries = $scores->map(function (Collection $userScores, string $userId) {
            $weightedTotal = 0;
            $weightSum = 0;

            foreach ($userScores as $score) {
                $weight = (float) ($score->indicator?->weight ?? 1);
                $weightedTotal += (float) $score->score * $weight;
                $weightSum += $weight;
            }

            return [
                'user_id' => $userId,
                'period' => $userScores->first()?->period,
                'overall_score' => $weightSum > 0 ? round($weightedTotal / $weightSum, 2) : 0,
                'score_count' => $userScores->count(),
            ];
        })->values()->all();

        return ['summaries' => $summaries];
    }

    /** @return array<string, mixed> */
    public function workSummary(User $user, string $period): array
    {
        [$year, $quarterOrMonth] = $this->parsePeriod($period);

        $tasksQuery = Task::query()->where('assignee_user_id', $user->id);
        $attendanceQuery = AttendanceRecord::query()->where('user_id', $user->id);
        $worklogQuery = Worklog::query()->where('user_id', $user->id);

        if (str_contains($period, 'Q')) {
            $startMonth = (($quarterOrMonth - 1) * 3) + 1;
            $endMonth = $startMonth + 2;
            $tasksQuery->whereYear('created_at', $year)->whereMonth('created_at', '>=', $startMonth)->whereMonth('created_at', '<=', $endMonth);
            $attendanceQuery->whereYear('date', $year)->whereMonth('date', '>=', $startMonth)->whereMonth('date', '<=', $endMonth);
            $worklogQuery->whereYear('logged_date', $year)->whereMonth('logged_date', '>=', $startMonth)->whereMonth('logged_date', '<=', $endMonth);
        } else {
            $tasksQuery->whereYear('created_at', $year)->whereMonth('created_at', $quarterOrMonth);
            $attendanceQuery->whereYear('date', $year)->whereMonth('date', $quarterOrMonth);
            $worklogQuery->whereYear('logged_date', $year)->whereMonth('logged_date', $quarterOrMonth);
        }

        $totalTasks = (clone $tasksQuery)->count();
        $onTimeTasks = (clone $tasksQuery)
            ->where('status', TaskStatus::Done)
            ->whereNotNull('due_date')
            ->whereColumn('completed_at', '<=', 'due_date')
            ->count();

        $presentDays = (clone $attendanceQuery)
            ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
            ->count();

        $workDays = (clone $attendanceQuery)->count();
        $worklogHours = round(((clone $worklogQuery)->sum('duration_minutes') / 60), 2);

        return [
            'user_id' => $user->id,
            'period' => $period,
            'tasks' => [
                'total' => $totalTasks,
                'on_time_completed' => $onTimeTasks,
                'on_time_percent' => $totalTasks > 0 ? round(($onTimeTasks / $totalTasks) * 100, 2) : 0,
            ],
            'attendance' => [
                'present_days' => $presentDays,
                'work_days' => $workDays,
                'attendance_percent' => $workDays > 0 ? round(($presentDays / $workDays) * 100, 2) : 0,
            ],
            'worklog_hours' => $worklogHours,
        ];
    }

    public function calculateAutoScore(PerformanceIndicator $indicator, User $user, string $period): PerformanceScore
    {
        $summary = $this->workSummary($user, $period);

        $score = match ($indicator->data_source) {
            PerformanceDataSource::AutoTasks => $summary['tasks']['on_time_percent'],
            PerformanceDataSource::AutoAttendance => $summary['attendance']['attendance_percent'],
            PerformanceDataSource::AutoWorklog => min(100, $summary['worklog_hours']),
            default => 0,
        };

        return PerformanceScore::query()->updateOrCreate(
            [
                'tenant_id' => app('tenant')->id,
                'user_id' => $user->id,
                'indicator_id' => $indicator->id,
                'period' => $period,
            ],
            [
                'score' => $score,
                'evidence' => 'Auto-calculated from '.$indicator->data_source->value,
                'evaluator_user_id' => $user->id,
            ]
        );
    }

    /** @return array{0: int, 1: int} */
    private function parsePeriod(string $period): array
    {
        if (str_contains($period, 'Q')) {
            [$year, $q] = explode('-Q', $period);

            return [(int) $year, (int) $q];
        }

        [$year, $month] = explode('-', $period);

        return [(int) $year, (int) $month];
    }
}
