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
        Schema::create('provider_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->decimal('balance', 8, 2)->default(0.0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('serviceman_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('serviceman_id')->nullable();
            $table->decimal('balance', 8, 2)->default(0.0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('serviceman_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_wallets');
        Schema::dropIfExists('serviceman_wallets');
    }
};
