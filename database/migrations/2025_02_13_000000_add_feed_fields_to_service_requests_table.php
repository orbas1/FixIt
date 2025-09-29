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
        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'attachments')) {
                $table->json('attachments')->nullable()->after('category_ids');
            }

            if (!Schema::hasColumn('service_requests', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('attachments');
            }

            if (!Schema::hasColumn('service_requests', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }

            if (!Schema::hasColumn('service_requests', 'h3_index')) {
                $table->unsignedBigInteger('h3_index')->nullable()->after('longitude');
                $table->index('h3_index', 'service_requests_h3_index_index');
            }

            if (!Schema::hasColumn('service_requests', 'location_coordinates')) {
                $table->json('location_coordinates')->nullable()->after('h3_index');
            }

            $table->index(['status', 'created_at'], 'service_requests_status_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'attachments')) {
                $table->dropColumn('attachments');
            }

            if (Schema::hasColumn('service_requests', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('service_requests', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('service_requests', 'h3_index')) {
                $table->dropIndex('service_requests_h3_index_index');
                $table->dropColumn('h3_index');
            }

            if (Schema::hasColumn('service_requests', 'location_coordinates')) {
                $table->dropColumn('location_coordinates');
            }

            $table->dropIndex('service_requests_status_created_at_index');
        });
    }
};
