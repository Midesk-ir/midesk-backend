<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_contacts')) {
            Schema::create('crm_contacts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('name');
                $table->string('company')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('type')->default('lead');
                $table->string('source')->nullable();
                $table->foreignUuid('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('tags')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'type']);
            });
        }

        if (! Schema::hasTable('crm_opportunities')) {
            Schema::create('crm_opportunities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('contact_id')->constrained('crm_contacts')->cascadeOnDelete();
                $table->string('title');
                $table->decimal('value', 15, 2)->default(0);
                $table->string('currency', 10)->default('IRR');
                $table->string('stage')->default('lead');
                $table->unsignedTinyInteger('probability')->default(0);
                $table->date('expected_close_date')->nullable();
                $table->foreignUuid('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'stage']);
            });
        }

        if (! Schema::hasTable('crm_activities')) {
            Schema::create('crm_activities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
                $table->foreignUuid('opportunity_id')->nullable()->constrained('crm_opportunities')->nullOnDelete();
                $table->string('type');
                $table->text('description');
                $table->foreignUuid('performed_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('performed_at');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_opportunities');
        Schema::dropIfExists('crm_contacts');
    }
};
