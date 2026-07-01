<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenant_id');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('invoice_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10);
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->date('due_date');
            $table->string('status')->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('payable_type');
            $table->uuid('payable_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10);
            $table->string('method');
            $table->string('reference')->nullable();
            $table->foreignUuid('paid_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('paid_at');
            $table->string('receipt_path')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'payable_type', 'payable_id']);
        });

        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->json('items');
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 10);
            $table->foreignUuid('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->foreignUuid('budget_id')->nullable()->constrained('budgets')->nullOnDelete();
            $table->text('justification')->nullable();
            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->foreignUuid('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->json('items');
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 10);
            $table->string('status')->default('draft');
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('vendors');
    }
};
