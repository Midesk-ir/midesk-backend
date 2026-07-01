<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kanban\DeleteKanbanColumnRequest;
use App\Http\Requests\Kanban\ReorderKanbanColumnsRequest;
use App\Http\Requests\Kanban\StoreKanbanColumnRequest;
use App\Http\Requests\Kanban\UpdateKanbanColumnRequest;
use App\Http\Resources\KanbanColumnResource;
use App\Models\KanbanColumn;
use App\Models\Project;
use App\Services\KanbanColumnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KanbanColumnController extends Controller
{
    public function __construct(
        private readonly KanbanColumnService $kanbanService,
    ) {}

    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $columns = KanbanColumn::query()
            ->where('project_id', $project->id)
            ->withCount('tasks')
            ->orderBy('order_index')
            ->get()
            ->map(fn (KanbanColumn $col) => tap($col, fn ($c) => $c->current_count = $c->tasks_count));

        return KanbanColumnResource::collection($columns);
    }

    public function store(StoreKanbanColumnRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [KanbanColumn::class, $project]);

        $column = $this->kanbanService->createColumn($project, $request->validated());

        return response()->json(new KanbanColumnResource($column), 201);
    }

    public function update(UpdateKanbanColumnRequest $request, KanbanColumn $column): JsonResponse
    {
        $this->authorize('update', $column);

        $column = $this->kanbanService->updateColumn($column, $request->validated());

        return response()->json(new KanbanColumnResource($column));
    }

    public function destroy(DeleteKanbanColumnRequest $request, KanbanColumn $column): JsonResponse
    {
        $this->authorize('delete', $column);

        $this->kanbanService->deleteColumn($column, $request->validated('target_column_id'));

        return response()->json(null, 204);
    }

    public function reorder(ReorderKanbanColumnsRequest $request): JsonResponse
    {
        $this->kanbanService->reorderColumns($request->validated('column_ids'));

        return response()->json(['message' => 'ok']);
    }
}
