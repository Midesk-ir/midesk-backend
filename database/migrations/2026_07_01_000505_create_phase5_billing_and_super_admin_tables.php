<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plan_prices')) {
            Schema::create('plan_prices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
                $table->string('region', 10);
                $table->string('currency', 10);
                $table->decimal('monthly_amount', 15, 2);
                $table->decimal('yearly_amount', 15, 2);
                $table->timestamp('created_at')->nullable();

                $table->unique(['plan_id', 'region']);
            });
        }

        if (! Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10);
                $table->string('status')->default('pending');
                $table->string('gateway')->default('manual');
                $table->string('gateway_ref')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->date('due_date');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('licenses')) {
            Schema::create('licenses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('license_key')->unique();
                $table->string('type')->default('standard');
                $table->unsignedInteger('max_users')->default(25);
                $table->json('features')->nullable();
                $table->date('valid_from');
                $table->date('valid_until');
                $table->boolean('is_active')->default(true);
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('super_admins')) {
            Schema::create('super_admins', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('full_name');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admins');
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('plan_prices');
    }
};
