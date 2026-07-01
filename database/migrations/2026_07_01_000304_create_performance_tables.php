<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->decimal('weight', 5, 2);
            $table->string('measurement_unit')->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('data_source')->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenant_id');
        });

        Schema::create('performance_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('indicator_id')->constrained('performance_indicators')->cascadeOnDelete();
            $table->string('period');
            $table->decimal('score', 5, 2);
            $table->text('evidence')->nullable();
            $table->foreignUuid('evaluator_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'user_id', 'period']);
        });

        Schema::create('objectives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('period');
            $table->string('status')->default('draft');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->foreignUuid('parent_objective_id')->nullable()->constrained('objectives')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'period']);
        });

        Schema::create('key_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('objective_id')->constrained('objectives')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('target_value', 15, 2);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->string('unit');
            $table->decimal('weight', 5, 2)->default(1);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period');
            $table->string('status')->default('draft');
            $table->text('self_assessment_text')->nullable();
            $table->text('manager_feedback')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('key_results');
        Schema::dropIfExists('objectives');
        Schema::dropIfExists('performance_scores');
        Schema::dropIfExists('performance_indicators');
    }
};
