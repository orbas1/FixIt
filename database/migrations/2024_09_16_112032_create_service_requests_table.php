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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('title',255)->nullable();
            $table->longText('description')->nullable();
            $table->string('duration')->nullable();
            $table->string('duration_unit')->nullable();
            $table->string('required_servicemen')->nullable();
            $table->decimal('initial_price', 10, 2)->nullable();
            $table->decimal('final_price', 10, 2)->nullable();
            $table->enum('status', ['open', 'pending', 'closed'])->default('open');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->dateTime('booking_date')->nullable();
            $table->json('category_ids')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->decimal('amount',8,4)->nullable();
            $table->longText('description')->nullable();
            $table->enum('status',['rejected','accepted','requested'])->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_request_id')->references('id')->on('service_requests')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('bids'); 
    }
};
