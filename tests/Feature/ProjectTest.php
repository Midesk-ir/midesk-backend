<?php

namespace Tests\Feature;

use App\Enums\ProjectMemberRole;
use App\Models\Project;
use App\Models\User;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    public function test_user_can_create_and_list_projects(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/projects', [
                'name' => 'New Project',
                'key' => 'NEW',
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'New Project');

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_view_project_details(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $project = Project::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Detail Project',
            'key' => 'DET',
            'owner_user_id' => $user->id,
        ]);

        $project->members()->attach($user->id, [
            'tenant_id' => $tenant->id,
            'role' => ProjectMemberRole::Lead->value,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('key', 'DET');
    }
}
