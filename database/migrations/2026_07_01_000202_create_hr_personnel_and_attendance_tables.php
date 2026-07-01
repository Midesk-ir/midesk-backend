<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_code');
            $table->string('national_id')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->string('employment_type')->default('full_time');
            $table->string('status')->default('active');
            $table->string('department')->nullable();
            $table->string('job_title')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'user_id']);
            $table->unique(['tenant_id', 'employee_code']);
            $table->index('tenant_id');
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamp('break_start_at')->nullable();
            $table->timestamp('break_end_at')->nullable();
            $table->foreignUuid('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->string('status')->default('absent');
            $table->boolean('auto_checkout')->default(false);
            $table->unsignedInteger('effective_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'date']);
            $table->index('tenant_id');
        });

        Schema::create('leave_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->boolean('auto_approve')->default(false);
            $table->json('approval_chain')->nullable();
            $table->unsignedSmallInteger('min_notice_days')->default(0);
            $table->boolean('allow_negative_balance')->default(false);
            $table->boolean('prorate_first_year')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('personnel');
    }
};
