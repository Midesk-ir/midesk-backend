<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('department')->nullable();
            $table->unsignedSmallInteger('positions_count')->default(1);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('requisition_id')->nullable()->constrained('requisitions')->nullOnDelete();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignUuid('interviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('type')->default('in_person');
            $table->text('feedback')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenant_id');
        });

        Schema::create('onboarding_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('steps');
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('template_id')->constrained('onboarding_templates')->cascadeOnDelete();
            $table->unsignedSmallInteger('current_step')->default(0);
            $table->string('status')->default('in_progress');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('step_completions')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'user_id']);
        });

        Schema::create('hr_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->json('settings');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
        Schema::dropIfExists('onboarding_progress');
        Schema::dropIfExists('onboarding_templates');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('requisitions');
    }
};
