<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('title');
                $table->text('body');
                $table->string('color')->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->string('visibility')->default('private');
                $table->foreignUuid('team_id')->nullable()->constrained('teams')->nullOnDelete();
                $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->foreignUuid('task_id')->nullable()->constrained('tasks')->nullOnDelete();
                $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['tenant_id', 'visibility']);
            });
        }

        if (! Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('event_type');
                $table->json('channels');
                $table->timestamps();

                $table->unique(['tenant_id', 'user_id', 'event_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notes');
    }
};
