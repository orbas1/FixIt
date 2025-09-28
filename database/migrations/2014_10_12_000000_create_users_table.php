<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->longText('slug')->nullable();
            $table->integer('system_reserve')->default(0);
            $table->string('password')->nullable();
            $table->integer('served')->nullable();
            $table->bigInteger('phone')->nullable();
            $table->bigInteger('code')->nullable();
            $table->bigInteger('provider_id')->unsigned()->nullable();
            $table->boolean('status')->default(0);
            $table->boolean('is_featured')->default(0);
            $table->boolean('is_verified')->default(0);
            $table->enum('type', ['company', 'freelancer'])->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('experience_interval')->nullable();
            $table->integer('experience_duration')->nullable();
            $table->longText('description')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->rememberToken();

            $table->foreign('provider_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
