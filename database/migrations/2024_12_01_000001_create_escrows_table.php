<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consumer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 40)->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_released', 12, 2)->default(0);
            $table->decimal('amount_refunded', 12, 2)->default(0);
            $table->string('currency', 3);
            $table->string('hold_reference')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Compliance release deadline');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['service_request_id', 'consumer_id', 'provider_id'], 'escrows_job_consumer_provider_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};
