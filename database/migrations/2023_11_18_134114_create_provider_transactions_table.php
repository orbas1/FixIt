<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_wallet_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->decimal('amount', 8, 2)->default(0.0);
            $table->enum('type', ['credit', 'debit'])->nullable();
            $table->string('detail')->nullable();
            $table->unsignedBigInteger('from')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_wallet_id')->references('id')->on('provider_wallets')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('serviceman_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('serviceman_wallet_id')->nullable();
            $table->unsignedBigInteger('serviceman_id')->nullable();
            $table->decimal('amount', 8, 2)->default(0.0);
            $table->enum('type', ['credit', 'debit'])->nullable();
            $table->string('detail')->nullable();
            $table->unsignedBigInteger('from')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('serviceman_wallet_id')->references('id')->on('serviceman_wallets')->onDelete('cascade');
            $table->foreign('serviceman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_transactions');
        Schema::dropIfExists('serviceman_transactions');
    }
};
