<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->json('permissions');
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name')->default('Midesk');
            $table->string('support_email')->default('support@midesk.io');
            $table->unsignedSmallInteger('default_trial_days')->default(14);
            $table->boolean('maintenance_mode')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('custom_roles');
    }
};
