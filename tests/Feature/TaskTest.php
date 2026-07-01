<?php

namespace Tests\Feature;

use App\Enums\ProjectMemberRole;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskTest extends TestCase
{
    public function test_user_can_create_task_in_member_project(): void
    {
        Notification::fake();

        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project] = $this->createProjectWithColumns($user, $tenant);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/tasks', [
                'project_id' => $project->id,
                'title' => 'Implement API',
                'status' => TaskStatus::Todo->value,
                'assignee_user_id' => $user->id,
            ])
            ->assertCreated()
            ->assertJsonPath('title', 'Implement API');
    }

    public function test_user_cannot_create_task_in_non_member_project(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();

        $otherOwner = User::factory()->create();

        $project = Project::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Restricted Project',
            'key' => 'RES',
            'owner_user_id' => $otherOwner->id,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->postJson('/api/v1/tasks', [
                'project_id' => $project->id,
                'title' => 'Should Fail',
            ])
            ->assertForbidden();
    }

    public function test_user_can_move_task_on_board(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project, 'columns' => $columns] = $this->createProjectWithColumns($user, $tenant);

        $task = Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'title' => 'Move Me',
            'reporter_user_id' => $user->id,
            'status' => TaskStatus::Todo,
            'kanban_column_id' => $columns['todo']->id,
            'order_index' => 1024,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->patchJson("/api/v1/tasks/{$task->id}/move", [
                'status' => TaskStatus::InProgress->value,
                'order_index' => 2048,
            ])
            ->assertOk()
            ->assertJsonPath('status', TaskStatus::InProgress->value);
    }
}
