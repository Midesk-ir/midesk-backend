<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('base_salary', 15, 2);
            $table->string('currency', 10);
            $table->json('benefits')->nullable();
            $table->string('status')->default('active');
            $table->foreignUuid('renewed_from_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'status']);
        });

        Schema::create('hr_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignUuid('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'user_id']);
        });

        Schema::create('missions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('transport_type');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->foreignUuid('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
        Schema::dropIfExists('hr_documents');
        Schema::dropIfExists('contracts');
    }
};
