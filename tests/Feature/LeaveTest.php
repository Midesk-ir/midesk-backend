<?php

namespace Tests\Feature;

use App\Enums\LeaveRequestStatus;
use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Membership;
use App\Models\User;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    public function test_user_can_view_leave_types_and_balance(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Annual',
            'default_balance_days' => 20,
            'unit' => 'day',
        ]);

        LeaveBalance::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'year' => now()->year,
            'total' => 20,
            'used' => 0,
            'remaining' => 20,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson('/api/v1/leave/types')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson('/api/v1/leave/balance')
            ->assertOk()
            ->assertJsonPath('data.0.remaining', 20);
    }

    public function test_user_can_submit_leave_request(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Annual',
            'default_balance_days' => 20,
            'unit' => 'day',
        ]);

        LeaveBalance::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'year' => now()->year,
            'total' => 20,
            'used' => 0,
            'remaining' => 20,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/leave/requests', [
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addWeek()->toDateString(),
                'end_date' => now()->addWeek()->toDateString(),
                'note' => 'Personal day',
            ])
            ->assertCreated()
            ->assertJsonPath('status', LeaveRequestStatus::Pending->value);
    }

    public function test_manager_can_approve_leave_request(): void
    {
        ['user' => $member, 'tenant' => $tenant] = $this->createUserWithTenant();

        $manager = User::factory()->create(['password' => 'password']);
        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $manager->id,
            'role' => MembershipRole::Manager,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);
        $managerToken = $manager->createToken('test', ['tenant_id' => $tenant->id])->plainTextToken;

        $leaveType = LeaveType::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Annual',
            'default_balance_days' => 20,
            'unit' => 'day',
        ]);

        LeaveBalance::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'leave_type_id' => $leaveType->id,
            'year' => now()->year,
            'total' => 20,
            'used' => 0,
            'remaining' => 20,
        ]);

        $memberToken = $member->createToken('test', ['tenant_id' => $tenant->id])->plainTextToken;

        $response = $this->withTenantAuth($member, $tenant, $memberToken)
            ->postJson('/api/v1/leave/requests', [
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addWeek()->toDateString(),
                'end_date' => now()->addWeek()->toDateString(),
            ]);

        $requestId = $response->json('id');

        $this->withTenantAuth($manager, $tenant, $managerToken)
            ->postJson("/api/v1/leave/requests/{$requestId}/approve")
            ->assertOk()
            ->assertJsonPath('status', LeaveRequestStatus::Approved->value);
    }
}
