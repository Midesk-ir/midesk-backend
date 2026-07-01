<?php

namespace Tests\Feature;

use App\Enums\ApprovalWorkflowStatus;
use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Models\Budget;
use App\Models\CostCenter;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Membership;
use App\Models\Objective;
use App\Models\PerformanceIndicator;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ExpenseWorkflowService;
use Tests\TestCase;

class FinancePhase3Test extends TestCase
{
    private function createFinanceUser(Tenant $tenant, MembershipRole $role = MembershipRole::Finance): array
    {
        $user = User::factory()->create(['password' => 'password']);

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $token = $user->createToken('test', ['tenant_id' => $tenant->id])->plainTextToken;

        return compact('user', 'token');
    }

    public function test_finance_user_can_create_cost_center_and_budget(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        $finance = $this->createFinanceUser($tenant);

        $response = $this->withTenantAuth($finance['user'], $tenant, $finance['token'])
            ->postJson('/api/v1/finance/cost-centers', [
                'name' => 'Marketing',
                'code' => 'MKT-001',
                'manager_user_id' => $user->id,
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'Marketing');

        $this->withTenantAuth($finance['user'], $tenant, $finance['token'])
            ->postJson('/api/v1/finance/budgets', [
                'cost_center_id' => $response->json('id'),
                'year' => 2026,
                'amount' => 50_000_000,
                'currency' => 'IRR',
            ])
            ->assertCreated()
            ->assertJsonPath('amount', '50000000.00');
    }

    public function test_expense_approval_workflow_under_threshold(): void
    {
        ['user' => $owner, 'tenant' => $tenant, 'token' => $ownerToken] = $this->createUserWithTenant();
        $member = User::factory()->create();
        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'role' => MembershipRole::Member,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);
        $memberToken = $member->createToken('test', ['tenant_id' => $tenant->id])->plainTextToken;

        $center = CostCenter::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Ops',
            'code' => 'OPS-001',
        ]);

        $category = ExpenseCategory::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Travel',
        ]);

        $expenseResponse = $this->withTenantAuth($member, $tenant, $memberToken)
            ->postJson('/api/v1/finance/expenses', [
                'category_id' => $category->id,
                'cost_center_id' => $center->id,
                'amount' => 1_000_000,
                'currency' => 'IRR',
                'description' => 'Taxi fare',
            ])
            ->assertCreated();

        $expenseId = $expenseResponse->json('id');

        $this->withTenantAuth($member, $tenant, $memberToken)
            ->postJson("/api/v1/finance/expenses/{$expenseId}/submit")
            ->assertOk()
            ->assertJsonPath('status', ApprovalWorkflowStatus::ManagerReview->value);

        $expense = Expense::withoutGlobalScopes()->findOrFail($expenseId);
        app()->instance('tenant', $tenant);
        app()->instance('membership', Membership::where('tenant_id', $tenant->id)->where('user_id', $owner->id)->first());

        $approved = app(ExpenseWorkflowService::class)->approve($expense, $owner);
        $this->assertSame(ApprovalWorkflowStatus::Approved, $approved->status);
    }

    public function test_expense_pay_updates_budget_spent(): void
    {
        ['user' => $owner, 'tenant' => $tenant, 'token' => $ownerToken] = $this->createUserWithTenant();
        $finance = $this->createFinanceUser($tenant);

        $center = CostCenter::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Finance CC',
            'code' => 'FIN-001',
        ]);

        $budget = Budget::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'cost_center_id' => $center->id,
            'year' => 2026,
            'amount' => 10_000_000,
            'spent' => 0,
            'currency' => 'IRR',
            'status' => 'active',
        ]);

        $category = ExpenseCategory::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Supplies',
        ]);

        $expense = Expense::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'cost_center_id' => $center->id,
            'budget_id' => $budget->id,
            'amount' => 2_000_000,
            'currency' => 'IRR',
            'description' => 'Approved expense',
            'status' => ApprovalWorkflowStatus::Approved,
        ]);

        $this->withTenantAuth($finance['user'], $tenant, $finance['token'])
            ->postJson("/api/v1/finance/expenses/{$expense->id}/pay", [
                'method' => 'bank_transfer',
            ])
            ->assertOk()
            ->assertJsonPath('status', ApprovalWorkflowStatus::Paid->value);

        $this->assertEquals('2000000.00', $budget->fresh()->spent);
    }

    public function test_member_cannot_access_finance_invoices(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        Membership::where('tenant_id', $tenant->id)->where('user_id', $user->id)->update([
            'role' => MembershipRole::Member,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson('/api/v1/finance/invoices')
            ->assertForbidden();
    }

    public function test_budget_overview_returns_utilization(): void
    {
        ['tenant' => $tenant] = $this->createUserWithTenant();
        $finance = $this->createFinanceUser($tenant);

        $center = CostCenter::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'R&D',
            'code' => 'RD-001',
        ]);

        Budget::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'cost_center_id' => $center->id,
            'year' => 2026,
            'amount' => 100,
            'spent' => 42,
            'currency' => 'IRR',
            'status' => 'active',
        ]);

        $this->withTenantAuth($finance['user'], $tenant, $finance['token'])
            ->getJson('/api/v1/finance/budgets/overview?year=2026')
            ->assertOk()
            ->assertJsonPath('budgets.0.utilization_percent', 42);
    }

    public function test_finance_data_isolated_between_tenants(): void
    {
        ['tenant' => $tenantA] = $this->createUserWithTenant();
        ['tenant' => $tenantB, 'user' => $userB, 'token' => $tokenB] = $this->createUserWithTenant();
        $financeB = $this->createFinanceUser($tenantB);

        CostCenter::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Tenant A Center',
            'code' => 'A-001',
        ]);

        $response = $this->withTenantAuth($financeB['user'], $tenantB, $financeB['token'])
            ->getJson('/api/v1/finance/cost-centers')
            ->assertOk();

        $this->assertCount(0, $response->json('data') ?? $response->json());
    }
}
