<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sprints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->text('goal')->nullable();
            $table->string('status')->default('planning');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index(['tenant_id', 'project_id', 'status']);
        });

        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 20)->nullable();
            $table->decimal('order_index', 16, 4)->default(1024);
            $table->unsignedSmallInteger('wip_limit')->nullable();
            $table->boolean('is_done_column')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['project_id', 'slug']);
            $table->index(['tenant_id', 'project_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignUuid('sprint_id')->nullable()->after('project_id')->constrained('sprints')->nullOnDelete();
            $table->foreignUuid('kanban_column_id')->nullable()->after('status')->constrained('kanban_columns')->nullOnDelete();
            $table->unsignedSmallInteger('story_points')->nullable()->after('kanban_column_id');
            $table->timestamp('started_at')->nullable()->after('story_points');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });

        $this->migrateExistingTasksToKanbanColumns();

        Schema::create('worklogs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->date('logged_date');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'user_id', 'logged_date']);
            $table->index(['tenant_id', 'project_id']);
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('all_day')->default(false);
            $table->string('color', 20)->nullable();
            $table->string('recurrence_rule')->nullable();
            $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'start_at', 'end_at']);
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->date('date');
            $table->boolean('recurring_yearly')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('worklogs');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sprint_id');
            $table->dropConstrainedForeignId('kanban_column_id');
            $table->dropColumn(['story_points', 'started_at', 'completed_at']);
        });

        Schema::dropIfExists('kanban_columns');
        Schema::dropIfExists('sprints');
    }

    private function migrateExistingTasksToKanbanColumns(): void
    {
        if (! Schema::hasTable('projects') || ! Schema::hasTable('tasks')) {
            return;
        }

        $defaultColumns = [
            ['name' => 'Backlog', 'slug' => 'backlog', 'order_index' => 1024, 'is_done_column' => false],
            ['name' => 'To Do', 'slug' => 'todo', 'order_index' => 2048, 'is_done_column' => false],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'order_index' => 3072, 'is_done_column' => false],
            ['name' => 'Review', 'slug' => 'review', 'order_index' => 4096, 'is_done_column' => false],
            ['name' => 'Done', 'slug' => 'done', 'order_index' => 5120, 'is_done_column' => true],
        ];

        $projects = DB::table('projects')->get();

        foreach ($projects as $project) {
            $columnMap = [];

            foreach ($defaultColumns as $col) {
                $id = (string) Str::uuid();
                DB::table('kanban_columns')->insert([
                    'id' => $id,
                    'tenant_id' => $project->tenant_id,
                    'project_id' => $project->id,
                    'name' => $col['name'],
                    'slug' => $col['slug'],
                    'color' => null,
                    'order_index' => $col['order_index'],
                    'wip_limit' => null,
                    'is_done_column' => $col['is_done_column'],
                    'created_at' => now(),
                ]);
                $columnMap[$col['slug']] = $id;
            }

            $tasks = DB::table('tasks')->where('project_id', $project->id)->get();

            foreach ($tasks as $task) {
                $slug = $task->status ?: 'todo';
                $columnId = $columnMap[$slug] ?? $columnMap['todo'];

                DB::table('tasks')->where('id', $task->id)->update([
                    'kanban_column_id' => $columnId,
                ]);
            }
        }
    }
};
