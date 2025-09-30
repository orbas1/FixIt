<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_id');
            $table->string('engine', 50);
            $table->string('verdict', 32);
            $table->json('report')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->timestamps();

            $table->foreign('media_id')
                ->references('id')
                ->on('media')
                ->cascadeOnDelete();

            $table->index(['media_id', 'verdict']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_scans');
    }
};
