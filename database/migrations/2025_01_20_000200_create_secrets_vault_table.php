<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('secrets_vault', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('version');
            $table->text('value');
            $table->json('metadata')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secrets_vault');
    }
};
