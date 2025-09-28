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
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->decimal('price')->nullable();
            $table->longText('description')->nullable();
            $table->longText('disclaimer')->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('is_featured')->nullable()->default('0');
            $table->string('hexa_code')->nullable();
            $table->string('bg_color')->nullable()->default('primary');
            $table->string('slug')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->bigInteger('provider_id')->unsigned()->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('service_package_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_package_id');
            $table->unsignedBigInteger('service_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('service_package_id')->references('id')->on('service_packages')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_package_services');
        Schema::dropIfExists('service_packages');
    }
};
