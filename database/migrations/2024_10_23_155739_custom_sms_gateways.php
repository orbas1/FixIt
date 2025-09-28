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
        Schema::create('custom_sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('base_url');
            $table->string('method');
            $table->string('sid');
            $table->string('auth_token');
            $table->string('is_config');
            $table->string('from');
            $table->json('custom_keys')->nullable();
            $table->json('config')->nullable();
            $table->json('body')->nullable();
            $table->json('params')->nullable();
            $table->json('headers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
