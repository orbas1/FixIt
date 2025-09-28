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
        Schema::create('booking_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique()->nullable();
            $table->string('slug')->unique()->nullable();
            $table->integer('sequence')->nullable()->unique();
            $table->text('description')->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->integer('system_reserve')->default(0);
            $table->string('hexa_code')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('booking_number')->startingValue(1000)->unique()->nullable();
            $table->unsignedBigInteger('consumer_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->decimal('wallet_balance', 8, 2)->nullable();
            $table->double('convert_wallet_balance')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('service_package_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->decimal('service_price', 8, 2)->nullable();
            $table->enum('type', ['fixed', 'provider_site', 'remotely'])->default('fixed');
            $table->decimal('tax', 8, 2)->nullable();
            $table->decimal('per_serviceman_charge')->nullable();
            $table->decimal('coupon_total_discount')->nullable();
            $table->decimal('platform_fees')->nullable();
            $table->string('platform_fees_type')->nullable();
            $table->integer('required_servicemen')->nullable();
            $table->integer('total_extra_servicemen')->default(0);
            $table->integer('total_servicemen')->nullable();
            $table->decimal('total_extra_servicemen_charge')->nullable();
            $table->decimal('subtotal', 8, 2)->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->dateTime('date_time')->nullable();
            $table->unsignedBigInteger('booking_status_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable()->default('PENDING');
            $table->text('description')->nullable();
            $table->string('invoice_url')->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('consumer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('booking_status_id')->references('id')->on('booking_status')->onDelete('cascade');
        });

        Schema::create('booking_servicemen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('serviceman_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('serviceman_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('booking_service', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('service_package_id')->nullable();
            $table->decimal('service_price', 8, 2)->nullable();
            $table->decimal('tax', 8, 2)->nullable();
            $table->decimal('per_serviceman_charge')->nullable();
            $table->decimal('total_extra_servicemen')->nullable();
            $table->decimal('total_extra_servicemen_charge')->nullable();
            $table->decimal('subtotal', 8, 2)->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->dateTime('date_time')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('service_package_id')->references('id')->on('service_packages')->onDelete('cascade');
        });

        Schema::create('booking_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });

        Schema::create('booking_additional_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('additional_service_id');
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        
            // Foreign key constraints
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('additional_service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_status');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_servicemen');
        Schema::dropIfExists('booking_services');
        Schema::dropIfExists('booking_transactions');
        Schema::dropIfExists('booking_additional_services');
    }
};
