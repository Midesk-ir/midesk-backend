<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Enums\ProjectMemberRole;
use App\Models\Membership;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    public function test_user_cannot_access_other_tenant_project(): void
    {
        ['user' => $userA, 'tenant' => $tenantA, 'token' => $tokenA] = $this->createUserWithTenant();
        ['tenant' => $tenantB] = $this->createUserWithTenant(['email' => 'other@example.com']);

        app()->instance('tenant', $tenantB);

        $foreignProject = Project::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Foreign Project',
            'key' => 'FOR',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        app()->forgetInstance('tenant');

        $this->withTenantAuth($userA, $tenantA, $tokenA)
            ->getJson("/api/v1/projects/{$foreignProject->id}")
            ->assertNotFound();
    }

    public function test_tasks_are_scoped_to_current_tenant(): void
    {
        ['user' => $userA, 'tenant' => $tenantA, 'token' => $tokenA] = $this->createUserWithTenant();
        ['tenant' => $tenantB] = $this->createUserWithTenant(['email' => 'tenantb@example.com']);

        $projectA = Project::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Project A',
            'key' => 'PRA',
            'owner_user_id' => $userA->id,
        ]);

        $projectA->members()->attach($userA->id, [
            'tenant_id' => $tenantA->id,
            'role' => ProjectMemberRole::Lead->value,
        ]);

        app()->instance('tenant', $tenantA);
        $columns = app(\App\Services\KanbanColumnService::class)->createDefaultColumns($projectA);
        $todoColumn = $columns->firstWhere('slug', 'todo');

        Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenantA->id,
            'project_id' => $projectA->id,
            'title' => 'Visible Task',
            'reporter_user_id' => $userA->id,
            'kanban_column_id' => $todoColumn?->id,
        ]);

        Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenantB->id,
            'project_id' => Project::withoutGlobalScopes()->create([
                'tenant_id' => $tenantB->id,
                'name' => 'Project B',
                'key' => 'PRB',
                'owner_user_id' => User::factory()->create()->id,
            ])->id,
            'title' => 'Hidden Task',
            'reporter_user_id' => User::factory()->create()->id,
        ]);

        $response = $this->withTenantAuth($userA, $tenantA, $tokenA)
            ->getJson("/api/v1/projects/{$projectA->id}/tasks");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Visible Task', $response->json('data.0.title'));
    }
}
