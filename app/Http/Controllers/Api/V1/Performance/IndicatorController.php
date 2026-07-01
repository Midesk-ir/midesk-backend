<?php

namespace App\Http\Controllers\Api\V1\Performance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Performance\StorePerformanceIndicatorRequest;
use App\Http\Requests\Performance\UpdatePerformanceIndicatorRequest;
use App\Http\Resources\Performance\PerformanceIndicatorResource;
use App\Models\PerformanceIndicator;
use App\Services\PerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndicatorController extends Controller
{
    public function __construct(private PerformanceService $service) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', PerformanceIndicator::class);

        return PerformanceIndicatorResource::collection($this->service->listIndicators());
    }

    public function store(StorePerformanceIndicatorRequest $request): JsonResponse
    {
        $this->authorize('create', PerformanceIndicator::class);

        $indicator = $this->service->createIndicator($request->validated());

        return response()->json(new PerformanceIndicatorResource($indicator), 201);
    }

    public function update(UpdatePerformanceIndicatorRequest $request, PerformanceIndicator $indicator): JsonResponse
    {
        $this->authorize('update', $indicator);

        return response()->json(
            new PerformanceIndicatorResource($this->service->updateIndicator($indicator, $request->validated()))
        );
    }

    public function destroy(PerformanceIndicator $indicator): JsonResponse
    {
        $this->authorize('delete', $indicator);

        $this->service->deleteIndicator($indicator);

        return response()->json(null, 204);
    }
}
