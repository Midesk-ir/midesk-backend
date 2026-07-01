<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\PayExpenseRequest;
use App\Http\Requests\Finance\RejectExpenseRequest;
use App\Http\Requests\Finance\StoreExpenseCategoryRequest;
use App\Http\Requests\Finance\StoreExpenseRequest;
use App\Http\Requests\Finance\UpdateExpenseCategoryRequest;
use App\Http\Requests\Finance\UpdateExpenseRequest;
use App\Http\Resources\Finance\ExpenseCategoryResource;
use App\Http\Resources\Finance\ExpenseResource;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseController extends Controller
{
    public function __construct(private ExpenseWorkflowService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::query()
            ->with(['category', 'costCenter', 'budget', 'user'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->string('category_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->string('user_id'));
        }

        return ExpenseResource::collection($query->get());
    }

    public function my(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = Expense::query()
            ->with(['category', 'costCenter', 'budget'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return ExpenseResource::collection($expenses);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $this->service->create(
            $request->user(),
            $request->validated()
        );

        return response()->json(
            new ExpenseResource($expense->load(['category', 'costCenter', 'budget', 'user'])),
            201
        );
    }

    public function show(Expense $expense): JsonResponse
    {
        $this->authorize('view', $expense);

        return response()->json(
            new ExpenseResource($expense->load(['category', 'costCenter', 'budget', 'user', 'payments']))
        );
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        return response()->json(
            new ExpenseResource($this->service->update($expense, $request->validated()))
        );
    }

    public function submit(Expense $expense): JsonResponse
    {
        $this->authorize('submit', $expense);

        return response()->json(
            new ExpenseResource($this->service->submit($expense)->load(['category', 'costCenter', 'budget']))
        );
    }

    public function approve(Request $request, Expense $expense): JsonResponse
    {
        $this->authorize('approve', $expense);

        return response()->json(
            new ExpenseResource($this->service->approve($expense, $request->user())->load(['category', 'costCenter', 'budget']))
        );
    }

    public function reject(RejectExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('reject', $expense);

        return response()->json(
            new ExpenseResource(
                $this->service->reject($expense, $request->user(), $request->validated('reason'))
                    ->load(['category', 'costCenter', 'budget'])
            )
        );
    }

    public function pay(PayExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorize('pay', $expense);

        return response()->json(
            new ExpenseResource(
                $this->service->pay($expense, $request->user(), $request->validated())
                    ->load(['category', 'costCenter', 'budget', 'payments'])
            )
        );
    }

    public function categories(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Expense::class);

        $categories = ExpenseCategory::query()
            ->with('parent')
            ->orderBy('name')
            ->get();

        return ExpenseCategoryResource::collection($categories);
    }

    public function storeCategory(StoreExpenseCategoryRequest $request): JsonResponse
    {
        $this->authorize('manageCategories', Expense::class);

        $category = ExpenseCategory::create([
            'tenant_id' => app('tenant')->id,
            ...$request->validated(),
        ]);

        return response()->json(new ExpenseCategoryResource($category->load('parent')), 201);
    }

    public function updateCategory(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $this->authorize('manageCategories', Expense::class);

        $expenseCategory->update($request->validated());

        return response()->json(new ExpenseCategoryResource($expenseCategory->fresh('parent')));
    }

    public function destroyCategory(ExpenseCategory $expenseCategory): JsonResponse
    {
        $this->authorize('manageCategories', Expense::class);

        $expenseCategory->delete();

        return response()->json(null, 204);
    }

    public function summary(): JsonResponse
    {
        $this->authorize('viewAny', Expense::class);

        if (! app('membership')?->canManageFinance() && ! app('membership')?->isManagerOrAbove()) {
            abort(403);
        }

        return response()->json($this->service->summary());
    }
}
