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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('title')->nullable();
            $table->enum('type', ['fixed', 'free_service', 'percentage'])->default('fixed')->nullable();
            $table->decimal('amount', 15)->default(0)->nullable();
            $table->decimal('min_spend', 15)->default(0)->nullable();
            $table->integer('is_unlimited')->default(1)->nullable();
            $table->integer('usage_per_coupon')->default(0)->nullable();
            $table->integer('usage_per_customer')->default(0)->nullable();
            $table->integer('used')->default(0)->nullable();
            $table->integer('status')->default(1)->nullable();
            $table->integer('is_expired')->default(0)->nullable();
            $table->integer('is_apply_all')->default(0)->nullable();
            $table->integer('is_first_order')->default(0)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
