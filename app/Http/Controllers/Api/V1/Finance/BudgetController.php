<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreBudgetRequest;
use App\Http\Requests\Finance\UpdateBudgetRequest;
use App\Http\Resources\Finance\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BudgetController extends Controller
{
    public function __construct(private BudgetService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Budget::class);

        return BudgetResource::collection(
            $this->service->listBudgets($request->only(['year', 'cost_center_id']))
        );
    }

    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $this->authorize('create', Budget::class);

        $budget = $this->service->createBudget($request->validated());

        return response()->json(new BudgetResource($budget->load('costCenter')), 201);
    }

    public function show(Budget $budget): JsonResponse
    {
        $this->authorize('view', $budget);

        return response()->json(new BudgetResource($budget->load('costCenter')));
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): JsonResponse
    {
        $this->authorize('update', $budget);

        return response()->json(
            new BudgetResource($this->service->updateBudget($budget, $request->validated()))
        );
    }

    public function overview(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Budget::class);

        $year = $request->integer('year') ?: null;

        return response()->json($this->service->overview($year));
    }
}
