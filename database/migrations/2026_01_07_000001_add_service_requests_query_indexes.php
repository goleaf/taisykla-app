<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Additional performance indexes for service_requests table
     * to optimize common query patterns.
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Composite indexes for common query patterns
            // These optimize queries that filter by status along with another column
            $table->index(['customer_id', 'status'], 'sr_customer_status_idx');
            $table->index(['technician_id', 'status'], 'sr_technician_status_idx');
            $table->index(['scheduled_at', 'status'], 'sr_scheduled_status_idx');

            // Index for approval-related queries
            $table->index('approval_status', 'sr_approval_status_idx');

            // Composite index for date range queries with status
            $table->index(['created_at', 'status'], 'sr_created_status_idx');
        });

        // Add indexes to equipment table for common lookups
        Schema::table('equipment', function (Blueprint $table) {
            // Composite index for customer equipment with status
            if (!$this->indexExists('equipment', 'equipment_org_status_idx')) {
                $table->index(['organization_id', 'status'], 'equipment_org_status_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex('sr_customer_status_idx');
            $table->dropIndex('sr_technician_status_idx');
            $table->dropIndex('sr_scheduled_status_idx');
            $table->dropIndex('sr_approval_status_idx');
            $table->dropIndex('sr_created_status_idx');
        });

        Schema::table('equipment', function (Blueprint $table) {
            if ($this->indexExists('equipment', 'equipment_org_status_idx')) {
                $table->dropIndex('equipment_org_status_idx');
            }
        });
    }

    /**
     * Check if an index exists on a table.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        try {
            switch ($driver) {
                case 'sqlite':
                    $indexes = $connection->select("PRAGMA index_list({$table})");
                    return collect($indexes)->contains('name', $indexName);

                case 'mysql':
                case 'mariadb':
                    $indexes = $connection->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
                    return count($indexes) > 0;

                case 'pgsql':
                    $indexes = $connection->select(
                        "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                        [$table, $indexName]
                    );
                    return count($indexes) > 0;

                default:
                    return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
};
