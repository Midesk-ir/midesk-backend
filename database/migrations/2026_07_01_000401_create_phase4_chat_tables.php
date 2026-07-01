<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_channels')) {
            Schema::create('chat_channels', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('name')->nullable();
                $table->string('type');
                $table->text('description')->nullable();
                $table->string('avatar_url')->nullable();
                $table->boolean('is_archived')->default(false);
                $table->foreignUuid('created_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'last_message_at']);
            });
        }

        if (! Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('channel_id')->constrained('chat_channels')->cascadeOnDelete();
                $table->foreignUuid('sender_user_id')->constrained('users')->cascadeOnDelete();
                $table->text('body')->nullable();
                $table->string('type')->default('text');
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->foreignUuid('reply_to_id')->nullable()->constrained('chat_messages')->nullOnDelete();
                $table->timestamp('edited_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['channel_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('chat_channel_members')) {
            Schema::create('chat_channel_members', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignUuid('channel_id')->constrained('chat_channels')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('member');
                $table->timestamp('joined_at');
                $table->timestamp('muted_until')->nullable();
                $table->foreignUuid('last_read_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
                $table->timestamp('created_at')->nullable();

                $table->unique(['channel_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('chat_reactions')) {
            Schema::create('chat_reactions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('message_id')->constrained('chat_messages')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('emoji');
                $table->timestamp('created_at')->nullable();

                $table->unique(['message_id', 'user_id', 'emoji']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_reactions');
        Schema::dropIfExists('chat_channel_members');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_channels');
    }
};
