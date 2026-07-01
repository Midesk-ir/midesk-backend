<?php

namespace Tests\Feature;

use App\Enums\CandidateStatus;
use App\Enums\ContractType;
use App\Enums\EmploymentType;
use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Enums\PersonnelStatus;
use App\Models\Candidate;
use App\Models\LeaveType;
use App\Models\Membership;
use App\Models\OnboardingTemplate;
use App\Models\Personnel;
use App\Models\Requisition;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HrTest extends TestCase
{
    public function test_hr_manager_can_create_and_list_personnel(): void
    {
        ['tenant' => $tenant] = $this->createUserWithTenant();
        ['user' => $hrUser, 'token' => $hrToken] = $this->createHrManager($tenant);

        $employee = User::factory()->create();
        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $employee->id,
            'role' => MembershipRole::Member,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->postJson('/api/v1/hr/personnel', [
                'user_id' => $employee->id,
                'employee_code' => 'EMP-100',
                'hire_date' => now()->toDateString(),
                'employment_type' => EmploymentType::FullTime->value,
            ])
            ->assertCreated()
            ->assertJsonPath('employee_code', 'EMP-100');

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->getJson('/api/v1/hr/personnel')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_member_can_check_in_and_out(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        Shift::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Morning',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'is_default' => true,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/hr/attendance/check-in')
            ->assertOk()
            ->assertJsonStructure(['id', 'date', 'check_in_at', 'status']);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/hr/attendance/check-out')
            ->assertOk()
            ->assertJsonPath('check_out_at', fn ($v) => $v !== null);
    }

    public function test_hr_manager_can_manage_contracts(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['user' => $hrUser, 'token' => $hrToken] = $this->createHrManager($tenant);

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->postJson('/api/v1/hr/contracts', [
                'user_id' => $user->id,
                'type' => ContractType::Permanent->value,
                'start_date' => now()->toDateString(),
                'base_salary' => 1000000,
                'currency' => 'IRR',
            ])
            ->assertCreated();

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->getJson('/api/v1/hr/contracts')
            ->assertOk();
    }

    public function test_hr_manager_can_upload_and_download_document(): void
    {
        Storage::fake('local');

        ['user' => $user, 'tenant' => $tenant] = $this->createUserWithTenant();
        ['user' => $hrUser, 'token' => $hrToken] = $this->createHrManager($tenant);

        $response = $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->post('/api/v1/hr/documents', [
                'user_id' => $user->id,
                'category' => 'resume',
                'title' => 'Resume',
                'file' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
            ]);

        $response->assertCreated();
        $documentId = $response->json('id');

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->getJson("/api/v1/hr/documents/{$documentId}/download")
            ->assertOk()
            ->assertJsonStructure(['url']);
    }

    public function test_hr_manager_can_manage_leave_policies(): void
    {
        ['tenant' => $tenant] = $this->createUserWithTenant();
        ['user' => $hrUser, 'token' => $hrToken] = $this->createHrManager($tenant);

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Annual',
            'default_balance_days' => 20,
            'unit' => 'day',
        ]);

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->postJson('/api/v1/leave/policies', [
                'leave_type_id' => $leaveType->id,
                'auto_approve' => false,
                'approval_chain' => ['manager', 'hr'],
                'min_notice_days' => 2,
            ])
            ->assertCreated();

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->getJson('/api/v1/leave/policies')
            ->assertOk();
    }

    public function test_hr_manager_can_hire_candidate(): void
    {
        ['tenant' => $tenant] = $this->createUserWithTenant();
        ['user' => $hrUser, 'token' => $hrToken] = $this->createHrManager($tenant);

        OnboardingTemplate::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Default',
            'is_default' => true,
            'steps' => [
                ['title' => 'Welcome', 'assignee_role' => 'hr', 'due_days' => 3],
            ],
        ]);

        $requisition = Requisition::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Backend Dev',
            'positions_count' => 1,
            'created_by_user_id' => $hrUser->id,
        ]);

        $candidate = Candidate::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'requisition_id' => $requisition->id,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => CandidateStatus::Offer,
        ]);

        $this->withTenantAuth($hrUser, $tenant, $hrToken)
            ->postJson("/api/v1/hr/candidates/{$candidate->id}/hire")
            ->assertCreated()
            ->assertJsonStructure(['personnel', 'onboarding']);
    }

    public function test_dashboard_includes_phase2_fields(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        Personnel::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'employee_code' => 'EMP-001',
            'hire_date' => now()->toDateString(),
            'employment_type' => EmploymentType::FullTime,
            'status' => PersonnelStatus::Active,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson('/api/v1/dashboard/summary')
            ->assertOk()
            ->assertJsonStructure([
                'attendance_today',
                'leave_balances',
            ]);
    }

    public function test_tenant_isolation_for_personnel(): void
    {
        ['tenant' => $tenant1] = $this->createUserWithTenant();
        ['tenant' => $tenant2, 'user' => $user2, 'token' => $token2] = $this->createUserWithTenant();

        ['user' => $hrUser, 'token' => $hrToken] = $this->createHrManager($tenant1);

        $employee = User::factory()->create();
        Membership::create([
            'tenant_id' => $tenant1->id,
            'user_id' => $employee->id,
            'role' => MembershipRole::Member,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $createResponse = $this->withTenantAuth($hrUser, $tenant1, $hrToken)
            ->postJson('/api/v1/hr/personnel', [
                'user_id' => $employee->id,
                'employee_code' => 'EMP-200',
                'hire_date' => now()->toDateString(),
                'employment_type' => EmploymentType::FullTime->value,
            ]);

        $createResponse->assertCreated();
        $personnelId = $createResponse->json('id');

        $response = $this->withTenantAuth($user2, $tenant2, $token2)
            ->getJson("/api/v1/hr/personnel/{$personnelId}");

        $this->assertContains($response->status(), [403, 404]);
    }
}
