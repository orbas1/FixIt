<?php

use App\Models\User;
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
        Schema::create('video_consultations', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_id')->nullable();
            $table->string('agenda')->nullable();
            $table->string('topic')->nullable();
            $table->enum('platform',['google_meet','zoom'])?->default('zoom')->nullable();
            $table->enum('type',[1 => 'instant',2 => 'scheduled',3 => 'recurring'])->nullable();
            $table->string('duration')->nullable();
            $table->string('timezone')->nullable();
            $table->longText('password')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('pre_schedule')?->default(0)->nullable();
            $table->longText('schedule_for')->nullable();
            $table->longText('template_id')->nullable();
            $table->longText('start_url')->nullable();
            $table->longText('join_url')->nullable();
            $table->longText('event_id')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_consultations');
    }
};
