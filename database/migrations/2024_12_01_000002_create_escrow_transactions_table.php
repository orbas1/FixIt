<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escrow_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40)->index();
            $table->string('direction', 20);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('reference')->nullable()->index();
            $table->string('gateway_id')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrow_transactions');
    }
};
