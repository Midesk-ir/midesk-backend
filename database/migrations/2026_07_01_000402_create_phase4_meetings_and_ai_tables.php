<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('meetings')) {
            Schema::create('meetings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignUuid('organizer_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('start_at');
                $table->timestamp('end_at');
                $table->string('type')->default('internal');
                $table->string('provider')->default('jitsi');
                $table->string('room_url')->nullable();
                $table->string('room_id')->nullable();
                $table->string('status')->default('scheduled');
                $table->string('recording_url')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'start_at']);
            });
        }

        if (! Schema::hasTable('meeting_attendees')) {
            Schema::create('meeting_attendees', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('meeting_id')->constrained('meetings')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('status')->default('invited');
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('left_at')->nullable();

                $table->unique(['meeting_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('ai_requests')) {
            Schema::create('ai_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('type');
                $table->json('input_data');
                $table->json('result')->nullable();
                $table->string('model_used')->nullable();
                $table->unsignedInteger('tokens_used')->nullable();
                $table->string('status')->default('queued');
                $table->text('error_message')->nullable();
                $table->foreignUuid('requested_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->index(['tenant_id', 'requested_by_user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendees');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('ai_requests');
    }
};
