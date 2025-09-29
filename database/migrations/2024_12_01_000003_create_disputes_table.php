<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('against_id')->constrained('users')->cascadeOnDelete();
            $table->string('stage', 40)->index();
            $table->string('status', 40)->index();
            $table->string('reason_code', 80)->nullable();
            $table->text('summary')->nullable();
            $table->json('resolution')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('sla_timer_started_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
