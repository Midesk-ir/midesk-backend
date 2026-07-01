<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('name');
                $table->time('start_time');
                $table->time('end_time');
                $table->json('days_of_week')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('default_balance_days', 8, 2)->default(0);
                $table->string('unit')->default('day');
                $table->boolean('is_paid')->default(true);
                $table->boolean('requires_approval')->default(true);
                $table->unsignedSmallInteger('max_consecutive_days')->nullable();
                $table->timestamps();

                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('leave_balances')) {
            Schema::create('leave_balances', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
                $table->unsignedSmallInteger('year');
                $table->decimal('total', 8, 2)->default(0);
                $table->decimal('used', 8, 2)->default(0);
                $table->decimal('remaining', 8, 2)->default(0);
                $table->timestamp('created_at')->useCurrent();

                $table->unique(['tenant_id', 'user_id', 'leave_type_id', 'year']);
                $table->index('tenant_id');
            });
        }

        if (! Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->decimal('duration_days', 8, 2);
                $table->string('status')->default('pending');
                $table->text('note')->nullable();
                $table->foreignUuid('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['tenant_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('shifts');
    }
};
