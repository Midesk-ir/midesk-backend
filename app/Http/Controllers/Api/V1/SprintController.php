<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sprint\AddSprintTaskRequest;
use App\Http\Requests\Sprint\CloseSprintRequest;
use App\Http\Requests\Sprint\StoreSprintRequest;
use App\Http\Requests\Sprint\UpdateSprintRequest;
use App\Http\Resources\SprintResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Services\SprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SprintController extends Controller
{
    public function __construct(
        private readonly SprintService $sprintService,
    ) {}

    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $sprints = Sprint::query()
            ->where('project_id', $project->id)
            ->orderByDesc('start_date')
            ->get();

        return SprintResource::collection($sprints);
    }

    public function store(StoreSprintRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Sprint::class, $project]);

        $sprint = $this->sprintService->create($project, $request->validated());

        return response()->json(new SprintResource($sprint), 201);
    }

    public function show(Sprint $sprint): JsonResponse
    {
        $this->authorize('view', $sprint);

        return response()->json(
            new SprintResource($sprint->load(['tasks.assignee', 'tasks.labels', 'tasks.kanbanColumn']))
        );
    }

    public function update(UpdateSprintRequest $request, Sprint $sprint): JsonResponse
    {
        $this->authorize('update', $sprint);

        $sprint = $this->sprintService->update($sprint, $request->validated());

        return response()->json(new SprintResource($sprint));
    }

    public function activate(Sprint $sprint): JsonResponse
    {
        $this->authorize('update', $sprint);

        $sprint = $this->sprintService->activate($sprint);

        return response()->json(new SprintResource($sprint));
    }

    public function close(CloseSprintRequest $request, Sprint $sprint): JsonResponse
    {
        $this->authorize('update', $sprint);

        $sprint = $this->sprintService->close($sprint, $request->validated());

        return response()->json(new SprintResource($sprint));
    }

    public function destroy(Sprint $sprint): JsonResponse
    {
        $this->authorize('delete', $sprint);

        $this->sprintService->delete($sprint);

        return response()->json(null, 204);
    }

    public function addTask(AddSprintTaskRequest $request, Sprint $sprint): JsonResponse
    {
        $this->authorize('update', $sprint);

        $task = Task::findOrFail($request->validated('task_id'));
        $this->authorize('update', $task);

        $result = $this->sprintService->addTask($sprint, $task);

        return response()->json([
            'task' => new TaskResource($result['task']->load(['assignee', 'labels'])),
            'warnings' => $result['warnings'],
        ]);
    }

    public function removeTask(Sprint $sprint, Task $task): JsonResponse
    {
        $this->authorize('update', $sprint);
        $this->authorize('update', $task);

        $task = $this->sprintService->removeTask($sprint, $task);

        return response()->json(new TaskResource($task));
    }
}
