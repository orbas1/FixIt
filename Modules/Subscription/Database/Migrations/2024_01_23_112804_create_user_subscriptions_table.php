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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('user_plan_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('total', 10, 2);
            $table->unsignedInteger('allowed_max_services');
            $table->unsignedInteger('allowed_max_addresses');
            $table->unsignedInteger('allowed_max_servicemen');
            $table->unsignedInteger('allowed_max_service_packages');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_included_free_trial')->default(false);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable()->default('PENDING');
            $table->string('product_id')->unique()->nullable();
            $table->string('in_app_status')->nullable();
            $table->string('in_app_price')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_plan_id')->references('id')->on('plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
