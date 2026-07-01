<?php

namespace Tests\Feature;

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SprintTest extends TestCase
{
    public function test_user_can_create_and_activate_sprint(): void
    {
        Notification::fake();

        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project] = $this->createProjectWithColumns($user, $tenant);

        $response = $this->withTenantAuth($user, $tenant, $token)
            ->postJson("/api/v1/projects/{$project->id}/sprints", [
                'name' => 'Sprint 2',
                'goal' => 'Ship features',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addWeeks(2)->toDateString(),
            ]);

        $response->assertCreated()->assertJsonPath('name', 'Sprint 2');

        $sprintId = $response->json('id');

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson("/api/v1/sprints/{$sprintId}/activate")
            ->assertOk()
            ->assertJsonPath('status', SprintStatus::Active->value);
    }

    public function test_only_one_active_sprint_per_project(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project] = $this->createProjectWithColumns($user, $tenant);

        $sprintA = Sprint::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'name' => 'Active Sprint',
            'status' => SprintStatus::Active,
            'start_date' => now(),
            'end_date' => now()->addWeek(),
        ]);

        $sprintB = Sprint::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'name' => 'Planning Sprint',
            'status' => SprintStatus::Planning,
            'start_date' => now()->addWeeks(2),
            'end_date' => now()->addWeeks(4),
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson("/api/v1/sprints/{$sprintB->id}/activate")
            ->assertStatus(422);
    }

    public function test_backlog_returns_tasks_without_sprint(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project, 'columns' => $columns] = $this->createProjectWithColumns($user, $tenant);

        Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'title' => 'Backlog Task',
            'reporter_user_id' => $user->id,
            'status' => TaskStatus::Backlog,
            'kanban_column_id' => $columns['backlog']->id,
            'order_index' => 1024,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson("/api/v1/projects/{$project->id}/backlog")
            ->assertOk()
            ->assertJsonCount(1, 'tasks');
    }
}
