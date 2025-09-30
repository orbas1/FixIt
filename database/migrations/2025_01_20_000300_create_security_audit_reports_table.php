<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_audit_reports', function (Blueprint $table) {
            $table->id();
            $table->string('tool');
            $table->string('status');
            $table->unsignedInteger('issues_found')->default(0);
            $table->json('summary')->nullable();
            $table->string('executed_by')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_reports');
    }
};
