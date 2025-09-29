<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->json('document');
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id'], 'search_snapshots_unique_subject');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_snapshots');
    }
};
