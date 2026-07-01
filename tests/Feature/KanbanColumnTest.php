<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\KanbanColumn;
use App\Models\Task;
use Tests\TestCase;

class KanbanColumnTest extends TestCase
{
    public function test_move_task_respects_wip_limit(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project, 'columns' => $columns] = $this->createProjectWithColumns($user, $tenant);

        $inProgress = KanbanColumn::withoutGlobalScopes()->find($columns['in_progress']->id);
        $inProgress->update(['wip_limit' => 1]);

        Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'title' => 'Occupying Task',
            'reporter_user_id' => $user->id,
            'status' => TaskStatus::InProgress,
            'kanban_column_id' => $inProgress->id,
            'order_index' => 1024,
        ]);

        $task = Task::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'project_id' => $project->id,
            'title' => 'Move Me',
            'reporter_user_id' => $user->id,
            'status' => TaskStatus::Todo,
            'kanban_column_id' => $columns['todo']->id,
            'order_index' => 2048,
        ]);

        $this->withTenantAuth($user, $tenant, $token)
            ->patchJson("/api/v1/tasks/{$task->id}/move", [
                'kanban_column_id' => $inProgress->id,
                'order_index' => 3072,
            ])
            ->assertStatus(409);
    }

    public function test_board_returns_custom_columns(): void
    {
        ['user' => $user, 'tenant' => $tenant, 'token' => $token] = $this->createUserWithTenant();
        ['project' => $project] = $this->createProjectWithColumns($user, $tenant);

        $this->withTenantAuth($user, $tenant, $token)
            ->getJson("/api/v1/projects/{$project->id}/board")
            ->assertOk()
            ->assertJsonCount(5, 'columns')
            ->assertJsonPath('columns.0.slug', 'backlog');
    }
}
