<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('custom_reports')) {
            Schema::create('custom_reports', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('name');
                $table->string('data_source');
                $table->json('filters')->nullable();
                $table->json('columns')->nullable();
                $table->string('group_by')->nullable();
                $table->string('chart_type')->nullable();
                $table->json('schedule')->nullable();
                $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('project_files')) {
            Schema::create('project_files', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('folder_path')->nullable();
                $table->string('name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size');
                $table->string('mime_type');
                $table->unsignedInteger('version')->default(1);
                $table->foreignUuid('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('guest_accesses')) {
            Schema::create('guest_accesses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('email');
                $table->string('name');
                $table->string('token')->unique();
                $table->string('role')->default('viewer');
                $table->json('project_ids');
                $table->timestamp('expires_at');
                $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_accesses');
        Schema::dropIfExists('project_files');
        Schema::dropIfExists('custom_reports');
    }
};
