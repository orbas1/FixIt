<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_requests')) {
            return;
        }

        if ($this->indexExists('service_requests', 'service_requests_status_created_at_index')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->dropIndex('service_requests_status_created_at_index');
            });
        }

        if (! $this->indexExists('service_requests', 'service_requests_status_h3_created_at_index')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->index(['status', 'h3_index', 'created_at'], 'service_requests_status_h3_created_at_index');
            });
        }

        if (Schema::hasTable('bids') && ! $this->indexExists('bids', 'bids_request_status_created_index')) {
            Schema::table('bids', function (Blueprint $table) {
                $table->index(['service_request_id', 'status', 'created_at'], 'bids_request_status_created_index');
            });
        }

        if (Schema::hasTable('services') && ! $this->indexExists('services', 'services_provider_status_index')) {
            Schema::table('services', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'services_provider_status_index');
            });
        }

        if (Schema::hasTable('disputes') && ! $this->indexExists('disputes', 'disputes_request_stage_status_index')) {
            Schema::table('disputes', function (Blueprint $table) {
                $table->index(['service_request_id', 'stage', 'status'], 'disputes_request_stage_status_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_requests')) {
            if ($this->indexExists('service_requests', 'service_requests_status_h3_created_at_index')) {
                Schema::table('service_requests', function (Blueprint $table) {
                    $table->dropIndex('service_requests_status_h3_created_at_index');
                });
            }

            if (! $this->indexExists('service_requests', 'service_requests_status_created_at_index')) {
                Schema::table('service_requests', function (Blueprint $table) {
                    $table->index(['status', 'created_at'], 'service_requests_status_created_at_index');
                });
            }
        }

        if (Schema::hasTable('bids') && $this->indexExists('bids', 'bids_request_status_created_index')) {
            Schema::table('bids', function (Blueprint $table) {
                $table->dropIndex('bids_request_status_created_index');
            });
        }

        if (Schema::hasTable('services') && $this->indexExists('services', 'services_provider_status_index')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropIndex('services_provider_status_index');
            });
        }

        if (Schema::hasTable('disputes') && $this->indexExists('disputes', 'disputes_request_stage_status_index')) {
            Schema::table('disputes', function (Blueprint $table) {
                $table->dropIndex('disputes_request_stage_status_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $results = $connection->select(
                sprintf('SHOW INDEX FROM `%s` WHERE Key_name = ?', $table),
                [$indexName]
            );

            return count($results) > 0;
        }

        if ($driver === 'pgsql') {
            $results = $connection->select(
                'SELECT indexname FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return count($results) > 0;
        }

        if ($driver === 'sqlite') {
            $results = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($results as $result) {
                if (($result->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        // Fallback for other drivers
        try {
            $results = $connection->select(
                sprintf('SHOW INDEX FROM `%s` WHERE Key_name = ?', $table),
                [$indexName]
            );

            return count($results) > 0;
        } catch (Throwable $exception) {
            Log::warning('Index existence check failed', [
                'table' => $table,
                'index' => $indexName,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
};
