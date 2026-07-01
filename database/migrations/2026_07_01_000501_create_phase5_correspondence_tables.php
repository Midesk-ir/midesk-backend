<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('correspondences')) {
            Schema::create('correspondences', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('number');
                $table->unsignedInteger('number_seq')->default(0);
                $table->string('type');
                $table->string('subject');
                $table->text('body');
                $table->string('sender_info')->nullable();
                $table->string('receiver_info')->nullable();
                $table->string('priority')->default('normal');
                $table->string('status')->default('draft');
                $table->foreignUuid('parent_id')->nullable()->constrained('correspondences')->nullOnDelete();
                $table->json('attachments')->nullable();
                $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('signed_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'type', 'status']);
            });
        }

        if (! Schema::hasTable('correspondence_routing')) {
            Schema::create('correspondence_routing', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('correspondence_id')->constrained('correspondences')->cascadeOnDelete();
                $table->foreignUuid('from_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('to_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action');
                $table->text('note')->nullable();
                $table->string('status')->default('pending');
                $table->date('due_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['tenant_id', 'to_user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondence_routing');
        Schema::dropIfExists('correspondences');
    }
};
