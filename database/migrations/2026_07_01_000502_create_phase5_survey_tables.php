<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surveys')) {
            Schema::create('surveys', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('type')->default('survey');
                $table->boolean('is_anonymous')->default(false);
                $table->string('status')->default('draft');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }

        if (! Schema::hasTable('survey_questions')) {
            Schema::create('survey_questions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('survey_id')->constrained('surveys')->cascadeOnDelete();
                $table->string('title');
                $table->string('type');
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(true);
                $table->unsignedInteger('order_index')->default(0);
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('survey_responses')) {
            Schema::create('survey_responses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('survey_id')->constrained('surveys')->cascadeOnDelete();
                $table->foreignUuid('respondent_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('answers');
                $table->timestamp('submitted_at');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
