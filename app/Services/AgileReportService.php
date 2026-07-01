<?php

namespace App\Services;

use App\Models\KanbanColumn;
use App\Models\Sprint;
use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AgileReportService
{
    /** @return array<string, mixed> */
    public function burndown(Sprint $sprint): array
    {
        $tasks = Task::query()
            ->where('sprint_id', $sprint->id)
            ->with('kanbanColumn')
            ->get();

        $totalPoints = (int) $tasks->sum('story_points');
        $period = CarbonPeriod::create($sprint->start_date, $sprint->end_date);
        $daily = [];
        $completedByDate = [];

        foreach ($tasks as $task) {
            if ($task->completed_at && $task->story_points) {
                $date = $task->completed_at->toDateString();
                $completedByDate[$date] = ($completedByDate[$date] ?? 0) + $task->story_points;
            }
        }

        $cumulativeCompleted = 0;
        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $cumulativeCompleted += $completedByDate[$dateStr] ?? 0;

            $daily[] = [
                'date' => $dateStr,
                'remaining_points' => max(0, $totalPoints - $cumulativeCompleted),
                'completed_points' => $cumulativeCompleted,
            ];
        }

        $days = count($daily);
        $idealLine = [];
        foreach ($period as $index => $date) {
            $idealLine[] = [
                'date' => $date->toDateString(),
                'remaining_points' => $days > 1
                    ? round($totalPoints * (1 - $index / ($days - 1)), 2)
                    : 0,
            ];
        }

        return [
            'sprint' => [
                'id' => $sprint->id,
                'name' => $sprint->name,
                'start_date' => $sprint->start_date->toDateString(),
                'end_date' => $sprint->end_date->toDateString(),
                'total_points' => $totalPoints,
            ],
            'daily' => $daily,
            'ideal_line' => $idealLine,
        ];
    }

    /** @return array<string, mixed> */
    public function burnup(Sprint $sprint): array
    {
        $tasks = Task::query()
            ->where('sprint_id', $sprint->id)
            ->with('kanbanColumn')
            ->get();

        $period = CarbonPeriod::create($sprint->start_date, $sprint->end_date);
        $scope = (int) $tasks->sum('story_points');
        $daily = [];

        $doneByDate = [];
        foreach ($tasks as $task) {
            if ($task->completed_at && $task->kanbanColumn?->is_done_column && $task->story_points) {
                $date = $task->completed_at->toDateString();
                $doneByDate[$date] = ($doneByDate[$date] ?? 0) + $task->story_points;
            }
        }

        $cumulativeDone = 0;
        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $cumulativeDone += $doneByDate[$dateStr] ?? 0;

            $daily[] = [
                'date' => $dateStr,
                'scope_points' => $scope,
                'done_points' => $cumulativeDone,
            ];
        }

        return [
            'sprint' => [
                'id' => $sprint->id,
                'name' => $sprint->name,
            ],
            'daily' => $daily,
        ];
    }

    /** @return array<string, mixed> */
    public function velocity(string $projectId, int $limit = 5): array
    {
        $sprints = Sprint::query()
            ->where('project_id', $projectId)
            ->where('status', \App\Enums\SprintStatus::Closed)
            ->orderByDesc('end_date')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        $results = $sprints->map(function (Sprint $sprint) {
            $tasks = Task::query()->where('sprint_id', $sprint->id)->with('kanbanColumn')->get();

            $committed = (int) $tasks->sum('story_points');
            $completed = (int) $tasks
                ->filter(fn (Task $t) => $t->kanbanColumn?->is_done_column)
                ->sum('story_points');

            return [
                'id' => $sprint->id,
                'name' => $sprint->name,
                'committed_points' => $committed,
                'completed_points' => $completed,
            ];
        });

        $avg = $results->isEmpty() ? 0.0 : round($results->avg('completed_points'), 1);

        return [
            'sprints' => $results->all(),
            'average_velocity' => $avg,
        ];
    }

    /** @return array<string, mixed> */
    public function cumulativeFlow(string $projectId, ?string $from = null, ?string $to = null): array
    {
        $fromDate = $from ? Carbon::parse($from) : now()->subWeeks(4);
        $toDate = $to ? Carbon::parse($to) : now();

        $columns = KanbanColumn::query()
            ->where('project_id', $projectId)
            ->orderBy('order_index')
            ->get();

        $period = CarbonPeriod::create($fromDate, $toDate);
        $daily = [];

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $columnCounts = [];

            foreach ($columns as $column) {
                $count = Task::query()
                    ->where('project_id', $projectId)
                    ->where('kanban_column_id', $column->id)
                    ->whereDate('created_at', '<=', $dateStr)
                    ->where(function ($q) use ($dateStr) {
                        $q->whereNull('completed_at')
                            ->orWhereDate('completed_at', '>', $dateStr);
                    })
                    ->count();

                $columnCounts[$column->slug] = $count;
            }

            $daily[] = [
                'date' => $dateStr,
                'columns' => $columnCounts,
            ];
        }

        return [
            'project_id' => $projectId,
            'daily' => $daily,
        ];
    }
}
