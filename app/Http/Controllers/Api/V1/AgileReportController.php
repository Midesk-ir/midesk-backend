<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BurndownReportResource;
use App\Http\Resources\VelocityReportResource;
use App\Models\Project;
use App\Models\Sprint;
use App\Services\AgileReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgileReportController extends Controller
{
    public function __construct(
        private readonly AgileReportService $reportService,
    ) {}

    public function burndown(Sprint $sprint): JsonResponse
    {
        $this->authorize('view', $sprint);

        return response()->json(new BurndownReportResource($this->reportService->burndown($sprint)));
    }

    public function burnup(Sprint $sprint): JsonResponse
    {
        $this->authorize('view', $sprint);

        return response()->json($this->reportService->burnup($sprint));
    }

    public function velocity(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $limit = (int) $request->query('limit', 5);

        return response()->json(new VelocityReportResource($this->reportService->velocity($project->id, $limit)));
    }

    public function cfd(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json($this->reportService->cumulativeFlow(
            $project->id,
            $request->query('from'),
            $request->query('to'),
        ));
    }
}
