<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Services\SprintService;
use Illuminate\Http\JsonResponse;

class BacklogController extends Controller
{
    public function __construct(
        private readonly SprintService $sprintService,
    ) {}

    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $this->sprintService->backlog($project);

        return response()->json([
            'tasks' => TaskResource::collection($tasks),
        ]);
    }
}
