<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Models\User;
use Tests\TestCase;

class OrganizationInviteTest extends TestCase
{
    public function test_owner_can_invite_registered_user_by_email(): void
    {
        ['user' => $owner, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);

        $this->withTenantAuth($owner, $tenant, $token)
            ->postJson('/api/v1/orgs/current/members/invite', [
                'email' => $invitee->email,
                'role' => MembershipRole::Member->value,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('memberships', [
            'tenant_id' => $tenant->id,
            'user_id' => $invitee->id,
            'status' => MembershipStatus::Invited->value,
        ]);
    }

    public function test_owner_can_invite_registered_user_by_phone(): void
    {
        ['user' => $owner, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        $invitee = User::factory()->create([
            'email' => null,
            'phone' => '09123456789',
        ]);

        $this->withTenantAuth($owner, $tenant, $token)
            ->postJson('/api/v1/orgs/current/members/invite', [
                'phone' => $invitee->phone,
                'role' => MembershipRole::Member->value,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('memberships', [
            'tenant_id' => $tenant->id,
            'user_id' => $invitee->id,
            'status' => MembershipStatus::Invited->value,
        ]);
    }

    public function test_invite_returns_error_on_the_submitted_identifier(): void
    {
        ['user' => $owner, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $this->withTenantAuth($owner, $tenant, $token)
            ->postJson('/api/v1/orgs/current/members/invite', [
                'phone' => '09999999999',
                'role' => MembershipRole::Member->value,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error.details.0.field', 'phone');
    }
}
