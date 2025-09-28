<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 8, 2)->default(0.0)->nullable();
            $table->string('message')->nullable();
            $table->string('admin_message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->nullable()->default('pending');
            $table->unsignedBigInteger('provider_wallet_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->enum('payment_type', ['paypal', 'bank'])->nullable()->default('bank');
            $table->integer('is_used')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_wallet_id')->references('id')->on('provider_wallets')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('serviceman_withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 8, 2)->default(0.0)->nullable();
            $table->string('message')->nullable();
            $table->string('admin_message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->nullable()->default('pending');
            $table->unsignedBigInteger('serviceman_wallet_id')->nullable();
            $table->unsignedBigInteger('serviceman_id')->nullable();
            $table->enum('payment_type', ['paypal', 'bank'])->nullable()->default('bank');
            $table->integer('is_used_by_admin')->default(0);
            $table->integer('is_used')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('serviceman_wallet_id')->references('id')->on('serviceman_wallets')->onDelete('cascade');
            $table->foreign('serviceman_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
        Schema::dropIfExists('serviceman_withdraw_requests');
    }
};
