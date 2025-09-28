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
        Schema::create('coupon_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coupon_id')->unsigned();
                $table->unsignedBigInteger('user_id')->unsigned();
                $table->timestamps();
                $table->softDeletes();
    
                $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->nullable();
        });

        Schema::create('coupon_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id')->unsigned();
            $table->unsignedBigInteger('zone_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade')->nullable();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_users');
        Schema::dropIfExists('coupon_zones');
    }
};
