<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->foreignUuid('parent_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->foreignUuid('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['tenant_id', 'code']);
            $table->index('tenant_id');
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->foreignUuid('parent_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenant_id');
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('cost_center_id')->constrained('cost_centers')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('quarter')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('spent', 15, 2)->default(0);
            $table->string('currency', 10);
            $table->string('status')->default('active');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'cost_center_id', 'year']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->foreignUuid('budget_id')->nullable()->constrained('budgets')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10);
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('cost_centers');
    }
};
