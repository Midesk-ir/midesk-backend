<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Label\StoreLabelRequest;
use App\Http\Requests\Task\AttachLabelRequest;
use App\Http\Requests\Task\MoveTaskRequest;
use App\Http\Requests\Task\StoreChecklistItemRequest;
use App\Http\Requests\Task\StoreTaskCommentRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateChecklistItemRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskSprintRequest;
use App\Http\Resources\LabelResource;
use App\Http\Resources\TaskCommentResource;
use App\Http\Resources\TaskResource;
use App\Models\ChecklistItem;
use App\Models\KanbanColumn;
use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentMentionNotification;
use App\Services\KanbanColumnService;
use App\Services\SprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TaskController extends Controller
{
    public function __construct(
        private readonly KanbanColumnService $kanbanService,
        private readonly SprintService $sprintService,
    ) {}
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $tasks = QueryBuilder::for(Task::class)
            ->where('project_id', $project->id)
            ->allowedFilters(
                AllowedFilter::exact('status'),
                AllowedFilter::exact('assignee_user_id'),
                AllowedFilter::exact('priority'),
                AllowedFilter::exact('label_id', 'labels.id'),
            )
            ->allowedSorts('order_index', 'created_at')
            ->defaultSort('order_index')
            ->with(['assignee', 'labels'])
            ->withCount('comments')
            ->paginate();

        return TaskResource::collection($tasks);
    }

    public function board(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $columns = KanbanColumn::query()
            ->where('project_id', $project->id)
            ->withCount('tasks')
            ->orderBy('order_index')
            ->get();

        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->with(['assignee', 'labels', 'kanbanColumn'])
            ->orderBy('order_index')
            ->get();

        $swimlane = $request->query('swimlane');

        $columnData = $columns->map(function (KanbanColumn $column) use ($tasks, $swimlane) {
            $columnTasks = $tasks->where('kanban_column_id', $column->id);

            if ($swimlane === 'assignee') {
                $grouped = $columnTasks->groupBy(fn (Task $t) => $t->assignee_user_id ?? 'unassigned');

                return [
                    'id' => $column->id,
                    'name' => $column->name,
                    'slug' => $column->slug,
                    'color' => $column->color,
                    'order_index' => (float) $column->order_index,
                    'wip_limit' => $column->wip_limit,
                    'is_done_column' => $column->is_done_column,
                    'current_count' => $column->tasks_count,
                    'swimlanes' => $grouped->map(fn ($items, $key) => [
                        'key' => $key,
                        'tasks' => TaskResource::collection($items->values()),
                    ])->values(),
                ];
            }

            if ($swimlane === 'priority') {
                $grouped = $columnTasks->groupBy(fn (Task $t) => $t->priority->value);

                return [
                    'id' => $column->id,
                    'name' => $column->name,
                    'slug' => $column->slug,
                    'color' => $column->color,
                    'order_index' => (float) $column->order_index,
                    'wip_limit' => $column->wip_limit,
                    'is_done_column' => $column->is_done_column,
                    'current_count' => $column->tasks_count,
                    'swimlanes' => $grouped->map(fn ($items, $key) => [
                        'key' => $key,
                        'tasks' => TaskResource::collection($items->values()),
                    ])->values(),
                ];
            }

            return [
                'id' => $column->id,
                'name' => $column->name,
                'slug' => $column->slug,
                'color' => $column->color,
                'order_index' => (float) $column->order_index,
                'wip_limit' => $column->wip_limit,
                'is_done_column' => $column->is_done_column,
                'current_count' => $column->tasks_count,
                'tasks' => TaskResource::collection($columnTasks->values()),
            ];
        });

        return response()->json(['columns' => $columnData]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $project = Project::findOrFail($request->validated('project_id'));
        $this->authorize('create', [Task::class, $project]);

        $user = $request->user();
        $data = $request->validated();

        $status = isset($data['status'])
            ? (is_string($data['status']) ? TaskStatus::from($data['status']) : $data['status'])
            : TaskStatus::Todo;
        $column = $this->kanbanService->resolveColumnForProject($project, $status->value);

        $task = Task::create([
            ...collect($data)->except('label_ids')->all(),
            'reporter_user_id' => $user->id,
            'status' => $status,
            'kanban_column_id' => $column?->id,
        ]);

        if (! empty($data['label_ids'])) {
            $task->labels()->attach(collect($data['label_ids'])->mapWithKeys(fn ($id) => [
                $id => ['tenant_id' => app('tenant')->id],
            ])->all());
        }

        $this->notifyAssignee($task);

        return response()->json(
            new TaskResource($task->load(['assignee', 'labels', 'checklistItems'])->loadCount('comments')),
            201
        );
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json(
            new TaskResource($task->load(['assignee', 'reporter', 'labels', 'checklistItems', 'comments.author'])->loadCount('comments'))
        );
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $data = $request->validated();
        $previousAssignee = $task->assignee_user_id;

        $task->update(collect($data)->except('label_ids')->all());

        if (array_key_exists('label_ids', $data)) {
            $task->labels()->sync(collect($data['label_ids'] ?? [])->mapWithKeys(fn ($id) => [
                $id => ['tenant_id' => app('tenant')->id],
            ])->all());
        }

        if ($task->assignee_user_id && $task->assignee_user_id !== $previousAssignee) {
            $this->notifyAssignee($task);
        }

        return response()->json(
            new TaskResource($task->fresh()->load(['assignee', 'labels', 'checklistItems'])->loadCount('comments'))
        );
    }

    public function move(MoveTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $data = $request->validated();
        $column = null;

        if (! empty($data['kanban_column_id'])) {
            $column = KanbanColumn::query()
                ->where('project_id', $task->project_id)
                ->findOrFail($data['kanban_column_id']);

            $this->kanbanService->assertWipLimit($column, $task->id);

            $data['status'] = $this->kanbanService->syncTaskStatusFromColumn($column);
        } elseif (! empty($data['status'])) {
            $column = $this->kanbanService->resolveColumnForProject(
                $task->project,
                is_string($data['status']) ? $data['status'] : $data['status']->value
            );

            if ($column) {
                $this->kanbanService->assertWipLimit($column, $task->id);
                $data['kanban_column_id'] = $column->id;
            }
        }

        if ($column?->is_done_column && ! $task->completed_at) {
            $data['completed_at'] = now();
        } elseif ($column && ! $column->is_done_column) {
            $data['completed_at'] = null;
        }

        if ($column && $column->slug === TaskStatus::InProgress->value && ! $task->started_at) {
            $data['started_at'] = now();
        }

        $task->update(collect($data)->only(['status', 'kanban_column_id', 'order_index', 'started_at', 'completed_at'])->all());

        return response()->json(new TaskResource($task->fresh()->load(['assignee', 'labels', 'kanbanColumn'])));
    }

    public function updateSprint(UpdateTaskSprintRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->sprintService->assignTaskSprint($task, $request->validated('sprint_id'));

        return response()->json(new TaskResource($task->load(['assignee', 'labels', 'sprint'])));
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }

    public function storeChecklist(StoreChecklistItemRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $item = $task->checklistItems()->create($request->validated());

        return response()->json(new TaskResource($task->fresh()->load('checklistItems')), 201);
    }

    public function updateChecklist(UpdateChecklistItemRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $item = ChecklistItem::findOrFail($request->validated('id'));
        $item->update(collect($request->validated())->except('id')->all());

        return response()->json(new TaskResource($task->fresh()->load('checklistItems')));
    }

    public function destroyChecklist(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate(['id' => ['required', 'uuid', 'exists:checklist_items,id']]);

        ChecklistItem::findOrFail($request->input('id'))->delete();

        return response()->json(null, 204);
    }

    public function comments(Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        return TaskCommentResource::collection(
            $task->comments()->with('author')->get()
        );
    }

    public function storeComment(StoreTaskCommentRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $comment = $task->comments()->create([
            'author_user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'mentions' => $request->validated('mentions', []),
        ]);

        foreach ($request->validated('mentions', []) as $userId) {
            $mentioned = User::find($userId);
            if ($mentioned) {
                $mentioned->notify(new TaskCommentMentionNotification($task, $comment));
            }
        }

        return response()->json(new TaskCommentResource($comment->load('author')), 201);
    }

    public function attachLabel(AttachLabelRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $labelId = $request->validated('label_id');
        $task->labels()->syncWithoutDetaching([
            $labelId => ['tenant_id' => app('tenant')->id],
        ]);

        return response()->json(new TaskResource($task->fresh()->load('labels')));
    }

    public function detachLabel(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate(['label_id' => ['required', 'uuid', 'exists:labels,id']]);

        $task->labels()->detach($request->input('label_id'));

        return response()->json(new TaskResource($task->fresh()->load('labels')));
    }

    private function notifyAssignee(Task $task): void
    {
        if ($task->assignee_user_id) {
            $assignee = User::find($task->assignee_user_id);
            $assignee?->notify(new TaskAssignedNotification($task));
        }
    }
}
