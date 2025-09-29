<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->enum('type', ['buyer_provider', 'support']);
            $table->enum('status', ['open', 'pending_support', 'closed', 'archived'])->default('open');
            $table->string('subject')->nullable();
            $table->foreignId('opened_by_id')->constrained('users');
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('thread_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['consumer', 'provider', 'support']);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('muted_until')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->timestamps();

            $table->unique(['thread_id', 'user_id']);
        });

        Schema::create('thread_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('thread_id')->constrained('threads')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->json('attachments')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['thread_id', 'created_at']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('last_message_id')->references('id')->on('thread_messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['last_message_id']);
        });

        Schema::dropIfExists('thread_messages');
        Schema::dropIfExists('thread_participants');
        Schema::dropIfExists('threads');
    }
};
