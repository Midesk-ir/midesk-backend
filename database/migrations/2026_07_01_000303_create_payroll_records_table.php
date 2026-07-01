<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period');
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->decimal('base_salary', 15, 2);
            $table->decimal('overtime_hours', 10, 2)->default(0);
            $table->decimal('overtime_amount', 15, 2)->default(0);
            $table->json('allowances')->nullable();
            $table->json('deductions')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->string('currency', 10);
            $table->string('status')->default('draft');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['tenant_id', 'user_id', 'period']);
            $table->index(['tenant_id', 'period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
