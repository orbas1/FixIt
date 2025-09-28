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
        Schema::create('serviceman_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_history_id')->constrained('commission_histories','id')->onDelete('cascade');
            $table->foreignId('serviceman_id')->constrained('users', 'id')->onDelete('cascade');
            $table->decimal('commission', 8, 2)->default(0.0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceman_commissions');
    }
};
