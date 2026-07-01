<?php

namespace App\Services;

use App\Enums\LeaveRequestStatus;
use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use App\Notifications\SprintActivatedNotification;
use App\Notifications\TaskAddedToSprintNotification;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SprintService
{
    public function create(Project $project, array $data): Sprint
    {
        return Sprint::create([
            'tenant_id' => $project->tenant_id,
            'project_id' => $project->id,
            'name' => $data['name'],
            'goal' => $data['goal'] ?? null,
            'status' => SprintStatus::Planning,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);
    }

    public function update(Sprint $sprint, array $data): Sprint
    {
        $sprint->update($data);

        return $sprint->fresh();
    }

    public function activate(Sprint $sprint): Sprint
    {
        if ($sprint->status !== SprintStatus::Planning) {
            throw ValidationException::withMessages([
                'status' => [__('errors.sprint_activate_planning_only')],
            ]);
        }

        $activeExists = Sprint::query()
            ->where('project_id', $sprint->project_id)
            ->where('status', SprintStatus::Active)
            ->where('id', '!=', $sprint->id)
            ->exists();

        if ($activeExists) {
            throw ValidationException::withMessages([
                'status' => [__('errors.sprint_one_active_per_project')],
            ]);
        }

        $sprint->update(['status' => SprintStatus::Active]);

        $this->notifyProjectMembers($sprint, new SprintActivatedNotification($sprint));

        return $sprint->fresh();
    }

    public function close(Sprint $sprint, array $options): Sprint
    {
        if ($sprint->status !== SprintStatus::Active) {
            throw ValidationException::withMessages([
                'status' => [__('errors.sprint_close_active_only')],
            ]);
        }

        $moveTo = $options['move_to'] ?? 'backlog';
        $nextSprintId = $options['next_sprint_id'] ?? null;

        $incompleteTasks = Task::query()
            ->where('sprint_id', $sprint->id)
            ->whereDoesntHave('kanbanColumn', fn ($q) => $q->where('is_done_column', true))
            ->get();

        foreach ($incompleteTasks as $task) {
            if ($moveTo === 'next_sprint_id' && $nextSprintId) {
                $task->update(['sprint_id' => $nextSprintId]);
            } else {
                $task->update(['sprint_id' => null]);
            }
        }

        $doneTasks = Task::query()
            ->where('sprint_id', $sprint->id)
            ->whereHas('kanbanColumn', fn ($q) => $q->where('is_done_column', true))
            ->whereNull('completed_at')
            ->get();

        foreach ($doneTasks as $task) {
            $task->update(['completed_at' => now()]);
        }

        $sprint->update(['status' => SprintStatus::Closed]);

        return $sprint->fresh();
    }

    public function delete(Sprint $sprint): void
    {
        if ($sprint->status !== SprintStatus::Planning) {
            throw ValidationException::withMessages([
                'status' => [__('errors.sprint_delete_planning_only')],
            ]);
        }

        if ($sprint->tasks()->exists()) {
            throw ValidationException::withMessages([
                'sprint' => [__('errors.sprint_delete_has_tasks')],
            ]);
        }

        $sprint->delete();
    }

    /** @return array{task: Task, warnings: list<string>} */
    public function addTask(Sprint $sprint, Task $task): array
    {
        $warnings = [];

        if ($task->story_points === null) {
            $warnings[] = __('errors.sprint_task_missing_story_points');
        }

        $task->update(['sprint_id' => $sprint->id]);

        if ($task->assignee) {
            $task->assignee->notify(new TaskAddedToSprintNotification($task, $sprint));
        }

        return ['task' => $task->fresh(), 'warnings' => $warnings];
    }

    public function removeTask(Sprint $sprint, Task $task): Task
    {
        if ($task->sprint_id !== $sprint->id) {
            throw ValidationException::withMessages([
                'task' => [__('errors.sprint_task_not_in_sprint')],
            ]);
        }

        $task->update(['sprint_id' => null]);

        return $task->fresh();
    }

    public function assignTaskSprint(Task $task, ?string $sprintId): Task
    {
        if ($sprintId) {
            $sprint = Sprint::findOrFail($sprintId);

            if ($sprint->project_id !== $task->project_id) {
                throw ValidationException::withMessages([
                    'sprint_id' => [__('errors.sprint_project_mismatch')],
                ]);
            }
        }

        $task->update(['sprint_id' => $sprintId]);

        return $task->fresh();
    }

    /** @return Collection<int, Task> */
    public function backlog(Project $project): Collection
    {
        return Task::query()
            ->where('project_id', $project->id)
            ->whereNull('sprint_id')
            ->with(['assignee', 'labels', 'kanbanColumn'])
            ->orderBy('order_index')
            ->get();
    }

    private function notifyProjectMembers(Sprint $sprint, object $notification): void
    {
        $project = $sprint->project()->with('members')->first();

        $users = collect([$project->owner])->merge($project->members)->unique('id');

        foreach ($users as $user) {
            $user?->notify($notification);
        }
    }
}
