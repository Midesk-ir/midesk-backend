<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCostCenterRequest;
use App\Http\Requests\Finance\UpdateCostCenterRequest;
use App\Http\Resources\Finance\CostCenterResource;
use App\Models\CostCenter;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CostCenterController extends Controller
{
    public function __construct(private BudgetService $service) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CostCenter::class);

        return CostCenterResource::collection($this->service->listCostCenters());
    }

    public function store(StoreCostCenterRequest $request): JsonResponse
    {
        $this->authorize('create', CostCenter::class);

        $costCenter = $this->service->createCostCenter($request->validated());

        return response()->json(new CostCenterResource($costCenter->load(['parent', 'manager'])), 201);
    }

    public function show(CostCenter $costCenter): JsonResponse
    {
        $this->authorize('view', $costCenter);

        return response()->json(new CostCenterResource($costCenter->load(['parent', 'manager'])));
    }

    public function update(UpdateCostCenterRequest $request, CostCenter $costCenter): JsonResponse
    {
        $this->authorize('update', $costCenter);

        return response()->json(
            new CostCenterResource($this->service->updateCostCenter($costCenter, $request->validated()))
        );
    }

    public function destroy(CostCenter $costCenter): JsonResponse
    {
        $this->authorize('delete', $costCenter);

        $this->service->deleteCostCenter($costCenter);

        return response()->json(null, 204);
    }
}
