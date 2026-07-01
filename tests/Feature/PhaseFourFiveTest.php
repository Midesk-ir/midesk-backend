<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Models\ChatChannel;
use App\Models\GuestAccess;
use App\Models\Membership;
use App\Models\Project;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhaseFourFiveTest extends TestCase
{
    public function test_user_can_create_group_channel_and_send_message(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        $member = User::factory()->create();
        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $member->id,
            'role' => MembershipRole::Member,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $response = $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/chat/channels', [
                'type' => 'group',
                'name' => 'Team Chat',
                'member_ids' => [$member->id],
            ]);

        $response->assertCreated();
        $channelId = $response->json('id');

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson("/api/v1/chat/channels/{$channelId}/messages", [
                'body' => 'Hello team!',
                'type' => 'text',
            ])
            ->assertCreated()
            ->assertJsonPath('body', 'Hello team!');
    }

    public function test_chat_is_isolated_between_tenants(): void
    {
        ['user' => $userA, 'tenant' => $tenantA, 'token' => $tokenA] = $this->createUserWithTenant();
        ['tenant' => $tenantB] = $this->createUserWithTenant(['email' => 'b@example.com']);

        $foreignChannel = ChatChannel::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'type' => 'group',
            'name' => 'Foreign',
            'created_by_user_id' => User::factory()->create()->id,
        ]);

        $this->withTenantAuth($userA, $tenantA, $tokenA)
            ->getJson("/api/v1/chat/channels/{$foreignChannel->id}")
            ->assertNotFound();
    }

    public function test_user_can_create_and_start_meeting(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $create = $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/meetings', [
                'title' => 'Sprint Review',
                'start_at' => now()->addHour()->toIso8601String(),
                'end_at' => now()->addHours(2)->toIso8601String(),
                'provider' => 'jitsi',
                'attendee_ids' => [],
            ]);

        $create->assertCreated();
        $meetingId = $create->json('id');

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson("/api/v1/meetings/{$meetingId}/start")
            ->assertOk()
            ->assertJsonStructure(['room_url', 'room_id', 'status']);
    }

    public function test_ai_request_is_queued_with_null_provider_disabled(): void
    {
        Config::set('ai.provider', 'null');
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/ai/query', ['prompt' => 'Summarize tasks'])
            ->assertForbidden();
    }

    public function test_ai_request_processes_with_local_provider(): void
    {
        Config::set('ai.provider', 'local');
        Http::fake([
            '*' => Http::response(['response' => 'Mock AI summary of tasks']),
        ]);

        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $response = $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/ai/query', ['prompt' => 'Summarize tasks']);

        $response->assertStatus(202);
        $requestId = $response->json('id');

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson("/api/v1/ai/requests/{$requestId}")
            ->assertOk()
            ->assertJsonPath('status', 'completed');
    }

    public function test_guest_can_access_assigned_project_with_token(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        $project = Project::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Guest Project',
            'key' => 'GST',
            'owner_user_id' => $user->id,
        ]);

        $invite = $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/guests/invite', [
                'email' => 'guest@example.com',
                'name' => 'Guest User',
                'role' => 'viewer',
                'project_ids' => [$project->id],
                'expires_at' => now()->addWeek()->toIso8601String(),
            ]);

        $invite->assertCreated();
        $guestToken = $invite->json('token');

        $this->withHeader('X-Guest-Token', $guestToken)
            ->getJson('/api/v1/guest/projects')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_expired_guest_token_is_rejected(): void
    {
        ['user' => $user, 'tenant' => $tenant] = $this->createUserWithTenant();

        $guest = GuestAccess::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'email' => 'expired@example.com',
            'name' => 'Expired',
            'token' => 'expired-token-123',
            'role' => 'viewer',
            'project_ids' => [],
            'expires_at' => now()->subDay(),
            'created_by_user_id' => $user->id,
            'created_at' => now(),
        ]);

        $this->withHeader('X-Guest-Token', $guest->token)
            ->getJson('/api/v1/guest/projects')
            ->assertUnauthorized();
    }

    public function test_super_admin_can_login_and_access_dashboard(): void
    {
        SuperAdmin::create([
            'email' => 'admin@midesk.test',
            'password' => 'password',
            'full_name' => 'Super Admin',
            'is_active' => true,
        ]);

        $login = $this->postJson('/api/v1/super-admin/login', [
            'email' => 'admin@midesk.test',
            'password' => 'password',
        ]);

        $login->assertOk()->assertJsonStructure(['token', 'admin']);
        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/super-admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['tenants_count', 'mrr', 'arr']);
    }

    public function test_regular_user_cannot_access_super_admin_routes(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson('/api/v1/super-admin/dashboard')
            ->assertUnauthorized();
    }
}
