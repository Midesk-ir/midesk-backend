<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('region', 10)->default('IR');
            $table->string('locale', 10)->default('fa');
            $table->string('timezone', 64)->default('Asia/Tehran');
            $table->string('default_currency', 10)->default('IRR');
            $table->string('calendar_type', 20)->default('jalali');
            $table->foreignUuid('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('MEMBER');
            $table->string('status')->default('active');
            $table->string('invited_email')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
            $table->index('tenant_id');
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUuid('lead_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_team')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
            $table->index('tenant_id');
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('key', 20);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->foreignUuid('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'key']);
            $table->index('tenant_id');
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
            $table->index('tenant_id');
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20)->default('#6366f1');
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('backlog');
            $table->string('priority')->default('medium');
            $table->foreignUuid('assignee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('due_date')->nullable();
            $table->decimal('order_index', 16, 4)->default(1024);
            $table->foreignUuid('parent_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'project_id', 'status']);
            $table->index('assignee_user_id');
        });

        Schema::create('label_task', function (Blueprint $table) {
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('label_id')->constrained('labels')->cascadeOnDelete();

            $table->primary(['task_id', 'label_id']);
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_done')->default(false);
            $table->decimal('order_index', 16, 4)->default(1024);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('task_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->json('mentions')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier');
            $table->string('code', 10);
            $table->string('purpose');
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index(['identifier', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('label_task');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('otp_codes');
    }
};
