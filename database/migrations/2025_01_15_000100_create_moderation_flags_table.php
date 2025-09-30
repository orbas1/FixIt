<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_flags', function (Blueprint $table) {
            $table->id();
            $table->morphs('flaggable');
            $table->string('category', 120);
            $table->string('severity', 40);
            $table->string('action', 40);
            $table->json('reasons');
            $table->json('snapshot')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->index(['flaggable_type', 'flaggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_flags');
    }
};
