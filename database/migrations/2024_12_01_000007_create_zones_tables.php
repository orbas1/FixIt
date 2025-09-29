<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_request_zones')) {
            Schema::create('service_request_zones', function (Blueprint $table) {
                $table->foreignId('service_request_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->foreignId('zone_id')
                    ->constrained('zones')
                    ->cascadeOnDelete();
                $table->primary(['service_request_id', 'zone_id']);
            });
        }

        if (Schema::hasTable('zones') && !Schema::hasColumn('zones', 'geometry')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->json('geometry')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_request_zones')) {
            Schema::drop('service_request_zones');
        }

        if (Schema::hasTable('zones') && Schema::hasColumn('zones', 'geometry')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->dropColumn('geometry');
            });
        }
    }
};
