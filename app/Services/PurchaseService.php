<?php

namespace App\Services;

use App\Enums\ApprovalWorkflowStatus;
use App\Enums\PurchaseOrderStatus;
use App\Jobs\BudgetOverrunAlert;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Support\ApprovalWorkflow;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function createRequest(User $user, array $data): PurchaseRequest
    {
        if (empty($data['cost_center_id'])) {
            throw ValidationException::withMessages([
                'cost_center_id' => [__('errors.cost_center_required')],
            ]);
        }

        return PurchaseRequest::create([
            'tenant_id' => app('tenant')->id,
            'user_id' => $user->id,
            'status' => ApprovalWorkflowStatus::Draft,
            'currency' => $data['currency'] ?? app('tenant')->default_currency,
            ...$data,
        ]);
    }

    public function updateRequest(PurchaseRequest $request, array $data): PurchaseRequest
    {
        if ($request->status !== ApprovalWorkflowStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => [__('errors.purchase_request_not_editable')],
            ]);
        }

        $request->update($data);

        return $request->fresh(['user', 'costCenter', 'budget']);
    }

    public function submit(PurchaseRequest $request): PurchaseRequest
    {
        ApprovalWorkflow::assertTransition('submit', $request->status, ApprovalWorkflowStatus::ManagerReview);

        $request->update([
            'status' => ApprovalWorkflowStatus::ManagerReview,
            'rejection_reason' => null,
        ]);

        return $request->fresh();
    }

    public function ownerReview(PurchaseRequest $request, User $reviewer, string $action, ?string $reason = null): PurchaseRequest
    {
        if (! ApprovalWorkflow::canManagerReview($reviewer, $request->user, app('membership'))) {
            throw ValidationException::withMessages(['status' => [__('errors.forbidden')]]);
        }

        if ($action === 'reject') {
            ApprovalWorkflow::assertTransition('reject', $request->status, ApprovalWorkflowStatus::Rejected);
            $request->update([
                'status' => ApprovalWorkflowStatus::Rejected,
                'rejection_reason' => $reason,
            ]);

            return $request->fresh();
        }

        $target = ApprovalWorkflow::managerTargetStatus((float) $request->total_amount);
        ApprovalWorkflow::assertTransition('manager_approve', $request->status, $target);

        $request->update(['status' => $target]);

        if ($target === ApprovalWorkflowStatus::Approved && $request->budget_id) {
            BudgetOverrunAlert::dispatch($request->budget);
        }

        return $request->fresh();
    }

    public function financeReview(PurchaseRequest $request): PurchaseRequest
    {
        if (! ApprovalWorkflow::canFinanceReview(app('membership'))) {
            throw ValidationException::withMessages(['status' => [__('errors.forbidden')]]);
        }

        ApprovalWorkflow::assertTransition('finance_approve', $request->status, ApprovalWorkflowStatus::Approved);

        $request->update(['status' => ApprovalWorkflowStatus::Approved]);

        if ($request->budget_id) {
            BudgetOverrunAlert::dispatch($request->budget);
        }

        return $request->fresh();
    }

    public function cancel(PurchaseRequest $request): PurchaseRequest
    {
        if ($request->status === ApprovalWorkflowStatus::Paid) {
            throw ValidationException::withMessages(['status' => [__('errors.invalid_workflow_transition')]]);
        }

        $request->update(['status' => ApprovalWorkflowStatus::Rejected]);

        return $request->fresh();
    }

    /** @return Collection<int, PurchaseRequest> */
    public function listRequests(array $filters = []): Collection
    {
        $query = PurchaseRequest::query()->with(['user', 'costCenter', 'budget']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function createOrder(array $data): PurchaseOrder
    {
        return PurchaseOrder::create([
            'tenant_id' => app('tenant')->id,
            'status' => PurchaseOrderStatus::Draft,
            'currency' => $data['currency'] ?? app('tenant')->default_currency,
            ...$data,
        ]);
    }

    public function createOrderFromRequest(PurchaseRequest $request, Vendor $vendor): PurchaseOrder
    {
        if ($request->status !== ApprovalWorkflowStatus::Approved) {
            throw ValidationException::withMessages([
                'purchase_request' => [__('errors.purchase_request_not_approved')],
            ]);
        }

        if ($request->purchaseOrder()->exists()) {
            throw ValidationException::withMessages([
                'purchase_request' => [__('errors.purchase_order_exists')],
            ]);
        }

        return PurchaseOrder::create([
            'tenant_id' => app('tenant')->id,
            'purchase_request_id' => $request->id,
            'vendor_id' => $vendor->id,
            'items' => $request->items,
            'total_amount' => $request->total_amount,
            'currency' => $request->currency,
            'status' => PurchaseOrderStatus::Draft,
        ]);
    }

    public function updateOrder(PurchaseOrder $order, array $data): PurchaseOrder
    {
        if (isset($data['status']) && $data['status'] === PurchaseOrderStatus::Ordered->value) {
            $data['ordered_at'] = now();
        }

        if (isset($data['status']) && $data['status'] === PurchaseOrderStatus::Received->value) {
            $data['received_at'] = now();
        }

        $order->update($data);

        return $order->fresh(['vendor', 'purchaseRequest']);
    }

    /** @return Collection<int, PurchaseOrder> */
    public function listOrders(): Collection
    {
        return PurchaseOrder::query()->with(['vendor', 'purchaseRequest'])->orderByDesc('created_at')->get();
    }

    public function createVendor(array $data): Vendor
    {
        return Vendor::create([
            'tenant_id' => app('tenant')->id,
            'is_active' => true,
            ...$data,
        ]);
    }

    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);

        return $vendor->fresh();
    }

    public function deleteVendor(Vendor $vendor): void
    {
        if ($vendor->invoices()->exists() || $vendor->purchaseOrders()->exists()) {
            throw ValidationException::withMessages([
                'vendor' => [__('errors.vendor_has_records')],
            ]);
        }

        $vendor->delete();
    }

    /** @return Collection<int, Vendor> */
    public function listVendors(): Collection
    {
        return Vendor::query()->orderBy('name')->get();
    }
}
