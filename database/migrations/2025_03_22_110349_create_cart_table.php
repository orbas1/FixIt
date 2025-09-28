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
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('required_servicemen')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('service_type')->nullable();
            $table->timestamp('date_time')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->longText('custom_message')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('cart_servicemen', function (Blueprint $table) {
            $table->unsignedBigInteger('serviceman_id')->nullable();
            $table->unsignedBigInteger('cart_id')->nullable();

            $table->foreign('serviceman_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
        Schema::dropIfExists('cart_servicemen');
    }
};
