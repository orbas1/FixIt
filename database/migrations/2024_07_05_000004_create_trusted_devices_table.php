<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_identifier');
            $table->string('display_name')->nullable();
            $table->string('platform')->nullable();
            $table->string('trust_level')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('signals')->nullable();
            $table->string('enrolled_by')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_identifier']);
            $table->index(['user_id', 'trust_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
