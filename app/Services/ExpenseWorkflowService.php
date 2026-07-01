<?php

namespace App\Services;

use App\Enums\ApprovalWorkflowStatus;
use App\Enums\PaymentMethod;
use App\Jobs\BudgetOverrunAlert;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Support\ApprovalWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseWorkflowService
{
    public function create(User $user, array $data): Expense
    {
        $this->assertCostCenter($data['cost_center_id'] ?? null);

        return Expense::create([
            'tenant_id' => app('tenant')->id,
            'user_id' => $user->id,
            'status' => ApprovalWorkflowStatus::Draft,
            'currency' => $data['currency'] ?? app('tenant')->default_currency,
            ...$data,
        ]);
    }

    public function update(Expense $expense, array $data): Expense
    {
        if (! in_array($expense->status, [ApprovalWorkflowStatus::Draft, ApprovalWorkflowStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'status' => [__('errors.expense_not_editable')],
            ]);
        }

        if (isset($data['cost_center_id'])) {
            $this->assertCostCenter($data['cost_center_id']);
        }

        $expense->update($data);

        return $expense->fresh(['category', 'costCenter', 'budget', 'user']);
    }

    public function submit(Expense $expense): Expense
    {
        $this->assertCostCenter($expense->cost_center_id);
        ApprovalWorkflow::assertTransition('submit', $expense->status, ApprovalWorkflowStatus::ManagerReview);

        $expense->update([
            'status' => ApprovalWorkflowStatus::ManagerReview,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        return $expense->fresh();
    }

    public function approve(Expense $expense, User $reviewer): Expense
    {
        $status = $expense->status instanceof ApprovalWorkflowStatus
            ? $expense->status
            : ApprovalWorkflowStatus::from((string) $expense->status);

        $membership = $this->resolveMembership($reviewer);

        if (in_array($status, [ApprovalWorkflowStatus::Submitted, ApprovalWorkflowStatus::ManagerReview], true)) {
            $canApprove = ($membership && ($membership->isOwnerOrAdmin() || $membership->canManageFinance()))
                || ApprovalWorkflow::canManagerReview($reviewer, $expense->user, $membership)
                || ApprovalWorkflow::canFinanceReview($membership);

            if (! $canApprove) {
                throw ValidationException::withMessages(['status' => [__('errors.forbidden')]]);
            }

            $target = ApprovalWorkflow::managerTargetStatus((float) $expense->amount);
            ApprovalWorkflow::assertTransition('manager_approve', $status, $target);

            $expense->update([
                'status' => $target,
                'approved_at' => $target === ApprovalWorkflowStatus::Approved ? now() : null,
            ]);

            if ($target === ApprovalWorkflowStatus::Approved && $expense->budget_id) {
                BudgetOverrunAlert::dispatch($expense->budget);
            }

            return $expense->fresh();
        }

        if ($status === ApprovalWorkflowStatus::FinanceReview) {
            if (! ApprovalWorkflow::canFinanceReview($membership)) {
                throw ValidationException::withMessages(['status' => [__('errors.forbidden')]]);
            }

            ApprovalWorkflow::assertTransition('finance_approve', $status, ApprovalWorkflowStatus::Approved);

            $expense->update([
                'status' => ApprovalWorkflowStatus::Approved,
                'approved_at' => now(),
            ]);

            if ($expense->budget_id) {
                BudgetOverrunAlert::dispatch($expense->budget);
            }

            return $expense->fresh();
        }

        throw ValidationException::withMessages(['status' => [__('errors.invalid_workflow_transition')]]);
    }

    public function reject(Expense $expense, User $reviewer, string $reason): Expense
    {
        $membership = app('membership');
        $canReject = ApprovalWorkflow::canFinanceReview($membership)
            || ApprovalWorkflow::canManagerReview($reviewer, $expense->user, $membership);

        if (! $canReject) {
            throw ValidationException::withMessages(['status' => [__('errors.forbidden')]]);
        }

        ApprovalWorkflow::assertTransition('reject', $expense->status, ApprovalWorkflowStatus::Rejected);

        $expense->update([
            'status' => ApprovalWorkflowStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        return $expense->fresh();
    }

    public function pay(Expense $expense, User $payer, array $data = []): Expense
    {
        if (! app('membership')?->canManageFinance()) {
            throw ValidationException::withMessages(['status' => [__('errors.forbidden')]]);
        }

        if (! in_array($expense->status, [ApprovalWorkflowStatus::Approved, ApprovalWorkflowStatus::ReadyToPay], true)) {
            throw ValidationException::withMessages(['status' => [__('errors.invalid_workflow_transition')]]);
        }

        return DB::transaction(function () use ($expense, $payer, $data) {
            Payment::create([
                'tenant_id' => $expense->tenant_id,
                'payable_type' => Expense::class,
                'payable_id' => $expense->id,
                'amount' => $expense->amount,
                'currency' => $expense->currency,
                'method' => $data['method'] ?? PaymentMethod::BankTransfer,
                'reference' => $data['reference'] ?? null,
                'paid_by_user_id' => $payer->id,
                'paid_at' => now(),
                'receipt_path' => $data['receipt_path'] ?? null,
            ]);

            if ($expense->budget_id) {
                $budget = Budget::query()->lockForUpdate()->find($expense->budget_id);
                if ($budget) {
                    $budget->increment('spent', $expense->amount);
                }
            }

            $expense->update([
                'status' => ApprovalWorkflowStatus::Paid,
                'paid_at' => now(),
            ]);

            return $expense->fresh(['payments']);
        });
    }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $paid = Expense::query()
            ->where('status', ApprovalWorkflowStatus::Paid)
            ->whereNotNull('paid_at')
            ->get();

        $byCategory = $paid->groupBy('category_id')->map(fn ($items, $categoryId) => [
            'category_id' => $categoryId,
            'total' => $items->sum('amount'),
        ])->values();

        $byMonth = $paid->groupBy(fn (Expense $e) => $e->paid_at->format('Y-m'))->map(fn ($items, $month) => [
            'month' => $month,
            'total' => $items->sum('amount'),
        ])->values();

        $byCostCenter = $paid->groupBy('cost_center_id')->map(fn ($items, $costCenterId) => [
            'cost_center_id' => $costCenterId,
            'total' => $items->sum('amount'),
        ])->values();

        return [
            'by_category' => $byCategory,
            'by_month' => $byMonth,
            'by_cost_center' => $byCostCenter,
        ];
    }

    private function assertCostCenter(?string $costCenterId): void
    {
        if (! $costCenterId) {
            throw ValidationException::withMessages([
                'cost_center_id' => [__('errors.cost_center_required')],
            ]);
        }
    }

    private function resolveMembership(User $user): ?\App\Models\Membership
    {
        if (app()->bound('membership') && ($membership = app('membership'))) {
            return $membership;
        }

        $tenantId = app()->bound('tenant')
            ? app('tenant')->id
            : request()->header('X-Tenant-Id');

        if (! $tenantId) {
            return null;
        }

        return \App\Models\Membership::query()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
    }
}
