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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->decimal('price')->nullable();
            $table->boolean('status')->default(1);
            $table->string('duration')->nullable();
            $table->string('duration_unit')->nullable();
            $table->decimal('service_rate', 8, 2)->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->decimal('per_serviceman_commission',4,2)->nullable();
            $table->longText('description')->nullable();
            $table->longText('content')->nullable();
            $table->longText('speciality_description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('type', ['fixed', 'provider_site', 'remotely'])->default('fixed');
            $table->boolean('is_featured')->nullable()->default('0');
            $table->string('required_servicemen')->nullable();
            $table->boolean('is_advertised')->nullable()->default('0');
            $table->string('meta_title')->nullable();
            $table->string('slug')->nullable();
            $table->string('meta_description')->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->integer('is_random_related_services')->default(0)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('services')->onDelete('cascade');
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('category_id');

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->nullable();
        });

        Schema::create('service_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('tax_id');

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('cascade')->nullable();
        });

        Schema::create('related_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('related_service_id')->nullable();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->nullable();
            $table->foreign('related_service_id')->references('id')->on('services')->onDelete('cascade')->nullable();
        });

        Schema::create('services_coupons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('coupon_id')->unsigned()->nullable();
            $table->bigInteger('service_id')->unsigned()->nullable();

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        Schema::create('exclude_services_coupons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('coupon_id')->unsigned()->nullable();
            $table->bigInteger('service_id')->unsigned()->nullable();

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        Schema::create('advertisement_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertisement_id')->unsigned();
            $table->unsignedBigInteger('service_id')->unsigned();

            $table->foreign('advertisement_id')->references('id')->on('advertisements')->onDelete('cascade')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
        Schema::dropIfExists('related_services');
        Schema::dropIfExists('service_categories');
    }
};
