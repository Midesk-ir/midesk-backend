<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Performance\StoreKeyResultRequest;
use App\Http\Requests\Performance\StoreObjectiveRequest;
use App\Http\Requests\Performance\UpdateObjectiveRequest;
use App\Http\Resources\Performance\KeyResultResource;
use App\Http\Resources\Performance\ObjectiveResource;
use App\Models\Objective;
use App\Services\OkrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ObjectiveController extends Controller
{
    public function __construct(private OkrService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Objective::class);

        return ObjectiveResource::collection(
            $this->service->list($request->only(['user_id', 'period']))
        );
    }

    public function store(StoreObjectiveRequest $request): JsonResponse
    {
        $this->authorize('create', Objective::class);

        $objective = $this->service->create($request->user(), $request->validated());

        return response()->json(
            new ObjectiveResource($objective->load(['keyResults', 'user'])),
            201
        );
    }

    public function show(Objective $objective): JsonResponse
    {
        $this->authorize('view', $objective);

        return response()->json(
            new ObjectiveResource($objective->load(['keyResults', 'user']))
        );
    }

    public function update(UpdateObjectiveRequest $request, Objective $objective): JsonResponse
    {
        $this->authorize('update', $objective);

        return response()->json(
            new ObjectiveResource($this->service->update($objective, $request->validated()))
        );
    }

    public function destroy(Objective $objective): JsonResponse
    {
        $this->authorize('delete', $objective);

        $this->service->delete($objective);

        return response()->json(null, 204);
    }

    public function storeKeyResult(StoreKeyResultRequest $request, Objective $objective): JsonResponse
    {
        $this->authorize('update', $objective);

        $keyResult = $this->service->addKeyResult($objective, $request->validated());

        return response()->json(new KeyResultResource($keyResult), 201);
    }
}
