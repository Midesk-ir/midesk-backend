<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worklog\StoreWorklogRequest;
use App\Http\Requests\Worklog\UpdateWorklogRequest;
use App\Http\Resources\WorklogResource;
use App\Models\Membership;
use App\Models\Worklog;
use App\Services\WorklogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorklogController extends Controller
{
    public function __construct(
        private readonly WorklogService $worklogService,
    ) {}

    public function my(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Worklog::class);

        $worklogs = $this->worklogService->myWorklogs($request->user(), [
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'project_id' => $request->query('project_id'),
        ]);

        return response()->json(['data' => WorklogResource::collection($worklogs)]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewTeam', Worklog::class);

        $userIds = $this->resolveTeamUserIds();

        $worklogs = $this->worklogService->teamWorklogs($userIds, [
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'project_id' => $request->query('project_id'),
        ]);

        return response()->json(['data' => WorklogResource::collection($worklogs)]);
    }

    public function reports(Request $request): JsonResponse
    {
        $this->authorize('viewTeam', Worklog::class);

        $userIds = $this->resolveTeamUserIds();

        return response()->json(
            $this->worklogService->aggregateReport($userIds, [
                'from_date' => $request->query('from_date'),
                'to_date' => $request->query('to_date'),
                'project_id' => $request->query('project_id'),
            ])
        );
    }

    public function store(StoreWorklogRequest $request): JsonResponse
    {
        $this->authorize('create', Worklog::class);

        $worklog = $this->worklogService->create($request->user(), $request->validated());

        return response()->json(
            new WorklogResource($worklog->load(['project', 'task'])),
            201
        );
    }

    public function update(UpdateWorklogRequest $request, Worklog $worklog): JsonResponse
    {
        $this->authorize('update', $worklog);

        $worklog = $this->worklogService->update($worklog, $request->validated());

        return response()->json(new WorklogResource($worklog->load(['project', 'task'])));
    }

    public function destroy(Worklog $worklog): JsonResponse
    {
        $this->authorize('delete', $worklog);

        $this->worklogService->delete($worklog);

        return response()->json(null, 204);
    }

    /** @return list<string> */
    private function resolveTeamUserIds(): array
    {
        if (app('membership')?->isOwnerOrAdmin()) {
            return Membership::query()
                ->where('tenant_id', app('tenant')->id)
                ->where('status', 'active')
                ->pluck('user_id')
                ->all();
        }

        return Membership::query()
            ->where('tenant_id', app('tenant')->id)
            ->where('status', 'active')
            ->pluck('user_id')
            ->all();
    }
}
