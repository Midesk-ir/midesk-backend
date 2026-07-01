<?php

namespace Tests;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Enums\ProjectMemberRole;
use App\Models\KanbanColumn;
use App\Models\Membership;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Services\KanbanColumnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function createUserWithTenant(array $userAttrs = [], array $tenantAttrs = []): array
    {
        $user = User::factory()->create(array_merge([
            'password' => 'password',
        ], $userAttrs));

        $tenant = Tenant::factory()->create($tenantAttrs);

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => MembershipRole::Owner,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $token = $user->createToken('test', ['tenant_id' => $tenant->id])->plainTextToken;

        return compact('user', 'tenant', 'token');
    }

    protected function withTenantAuth(User $user, Tenant $tenant, string $token): static
    {
        return $this->withToken($token)->withHeader('X-Tenant-Id', $tenant->id);
    }

    protected function createHrManager(Tenant $tenant): array
    {
        $hrUser = User::factory()->create(['password' => 'password']);

        Membership::create([
            'tenant_id' => $tenant->id,
            'user_id' => $hrUser->id,
            'role' => MembershipRole::HrManager,
            'status' => MembershipStatus::Active,
            'joined_at' => now(),
        ]);

        $token = $hrUser->createToken('test', ['tenant_id' => $tenant->id])->plainTextToken;

        return ['user' => $hrUser, 'token' => $token];
    }

    /** @return array{project: Project, columns: \Illuminate\Support\Collection<string, KanbanColumn>} */
    protected function createProjectWithColumns(User $user, Tenant $tenant, array $projectAttrs = []): array
    {
        app()->instance('tenant', $tenant);

        $project = Project::withoutGlobalScopes()->create(array_merge([
            'tenant_id' => $tenant->id,
            'name' => 'Test Project',
            'key' => 'TST'.fake()->unique()->numberBetween(100, 999),
            'owner_user_id' => $user->id,
        ], $projectAttrs));

        $project->members()->attach($user->id, [
            'tenant_id' => $tenant->id,
            'role' => ProjectMemberRole::Lead->value,
        ]);

        $columns = app(KanbanColumnService::class)->createDefaultColumns($project)->keyBy('slug');

        return compact('project', 'columns');
    }
}
