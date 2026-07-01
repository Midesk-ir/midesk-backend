<?php

namespace App\Services;

use App\Enums\BudgetStatus;
use App\Models\Budget;
use App\Models\CostCenter;
use App\Models\Expense;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BudgetService
{
    public function createCostCenter(array $data): CostCenter
    {
        return CostCenter::create([
            'tenant_id' => app('tenant')->id,
            ...$data,
        ]);
    }

    public function updateCostCenter(CostCenter $costCenter, array $data): CostCenter
    {
        $costCenter->update($data);

        return $costCenter->fresh(['parent', 'manager']);
    }

    public function deleteCostCenter(CostCenter $costCenter): void
    {
        if ($costCenter->budgets()->exists()) {
            throw ValidationException::withMessages([
                'cost_center' => [__('errors.cost_center_has_budgets')],
            ]);
        }

        $costCenter->delete();
    }

    /** @return Collection<int, CostCenter> */
    public function listCostCenters(): Collection
    {
        return CostCenter::query()->with(['parent', 'manager'])->orderBy('name')->get();
    }

    public function createBudget(array $data): Budget
    {
        return Budget::create([
            'tenant_id' => app('tenant')->id,
            'currency' => $data['currency'] ?? app('tenant')->default_currency,
            'spent' => 0,
            'status' => BudgetStatus::Active,
            ...$data,
        ]);
    }

    public function updateBudget(Budget $budget, array $data): Budget
    {
        $budget->update($data);

        return $budget->fresh('costCenter');
    }

    /** @return Collection<int, Budget> */
    public function listBudgets(array $filters = []): Collection
    {
        $query = Budget::query()->with('costCenter');

        if (isset($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (isset($filters['cost_center_id'])) {
            $query->where('cost_center_id', $filters['cost_center_id']);
        }

        return $query->orderByDesc('year')->get();
    }

    /** @return array{budgets: list<array<string, mixed>>} */
    public function overview(?int $year = null): array
    {
        $year = $year ?? (int) now()->year;

        $budgets = Budget::query()
            ->with('costCenter')
            ->where('year', $year)
            ->get()
            ->map(function (Budget $budget) {
                $remaining = (float) $budget->amount - (float) $budget->spent;
                $utilization = $budget->amount > 0
                    ? round(((float) $budget->spent / (float) $budget->amount) * 100, 2)
                    : 0;

                return [
                    'id' => $budget->id,
                    'cost_center' => $budget->costCenter?->name,
                    'cost_center_id' => $budget->cost_center_id,
                    'year' => $budget->year,
                    'quarter' => $budget->quarter,
                    'amount' => (float) $budget->amount,
                    'spent' => (float) $budget->spent,
                    'remaining' => $remaining,
                    'utilization_percent' => (float) $utilization,
                    'currency' => $budget->currency,
                    'status' => $budget->status->value,
                ];
            })
            ->values()
            ->all();

        return ['budgets' => $budgets];
    }

    public function checkBudgetWarning(Budget $budget, float $additionalAmount): ?string
    {
        $projected = (float) $budget->spent + $additionalAmount;
        $threshold = (float) $budget->amount * (config('finance.budget_alert_percent', 80) / 100);

        if ($projected > (float) $budget->amount) {
            return __('errors.budget_exceeded');
        }

        if ($projected > $threshold) {
            return __('errors.budget_near_limit');
        }

        return null;
    }
}
