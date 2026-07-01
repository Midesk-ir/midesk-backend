<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Tests\TestCase;

class WorklogTest extends TestCase
{
    public function test_user_can_create_worklog(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project] = $this->createProjectWithColumns($user, $tenant);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/worklogs', [
                'project_id' => $project->id,
                'description' => 'Implemented API',
                'duration_minutes' => 120,
                'logged_date' => now()->toDateString(),
            ])
            ->assertCreated()
            ->assertJsonPath('duration_minutes', 120);
    }

    public function test_cannot_log_on_archived_project(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $project = Project::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Archived',
            'key' => 'ARC',
            'owner_user_id' => $user->id,
            'status' => ProjectStatus::Archived,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/worklogs', [
                'project_id' => $project->id,
                'duration_minutes' => 60,
                'logged_date' => now()->toDateString(),
            ])
            ->assertStatus(422);
    }
}
