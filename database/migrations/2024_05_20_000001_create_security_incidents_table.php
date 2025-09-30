<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('title');
            $table->string('severity');
            $table->string('status');
            $table->string('detection_source')->nullable();
            $table->json('impacted_assets')->nullable();
            $table->json('timeline')->nullable();
            $table->text('impact_summary')->nullable();
            $table->longText('mitigation_steps')->nullable();
            $table->longText('root_cause')->nullable();
            $table->json('follow_up_actions')->nullable();
            $table->json('runbook_updates')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};
