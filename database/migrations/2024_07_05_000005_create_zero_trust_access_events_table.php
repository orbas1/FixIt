<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zero_trust_access_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trusted_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('network_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('decision');
            $table->unsignedTinyInteger('risk_score');
            $table->json('signals');
            $table->string('policy_version')->nullable();
            $table->timestamp('mfa_verified_at')->nullable();
            $table->json('enforced_controls')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['decision', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zero_trust_access_events');
    }
};
