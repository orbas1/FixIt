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
        Schema::create('banner_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('banner_id')->unsigned();
            $table->unsignedBigInteger('zone_id')->unsigned();

            $table->foreign('banner_id')->references('id')->on('banners')->onDelete('cascade')->nullable();
            $table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_zones');
    }
};
