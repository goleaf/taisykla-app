<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Class DatabaseOptimizationService
 *
 * Provides utilities for analyzing and optimizing database queries.
 * Useful for development and debugging slow queries.
 */
class DatabaseOptimizationService
{
    /**
     * Threshold for slow queries in milliseconds.
     */
    protected const SLOW_QUERY_THRESHOLD_MS = 100;

    /**
     * Collected queries during analysis.
     */
    protected array $queries = [];

    /**
     * Whether query logging is enabled.
     */
    protected bool $loggingEnabled = false;

    /**
     * Enable query logging for analysis.
     *
     * @return void
     */
    public function enableLogging(): void
    {
        if ($this->loggingEnabled) {
            return;
        }

        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'connection' => $query->connectionName,
            ];
        });

        $this->loggingEnabled = true;
    }

    /**
     * Disable query logging.
     *
     * @return void
     */
    public function disableLogging(): void
    {
        DB::flushQueryLog();
        $this->loggingEnabled = false;
    }

    /**
     * Get all logged queries.
     *
     * @return array
     */
    public function getQueryLog(): array
    {
        return $this->queries;
    }

    /**
     * Clear the query log.
     *
     * @return void
     */
    public function clearQueryLog(): void
    {
        $this->queries = [];
    }

    /**
     * Analyze and return slow queries (exceeding threshold).
     *
     * @param float|null $thresholdMs Custom threshold in milliseconds
     * @return Collection
     */
    public function analyzeSlowQueries(?float $thresholdMs = null): Collection
    {
        $threshold = $thresholdMs ?? self::SLOW_QUERY_THRESHOLD_MS;

        return collect($this->queries)
            ->filter(fn($query) => $query['time'] > $threshold)
            ->sortByDesc('time')
            ->values();
    }

    /**
     * Get query statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $queries = collect($this->queries);

        if ($queries->isEmpty()) {
            return [
                'total_queries' => 0,
                'total_time_ms' => 0,
                'avg_time_ms' => 0,
                'slowest_query_ms' => 0,
                'slow_queries_count' => 0,
            ];
        }

        return [
            'total_queries' => $queries->count(),
            'total_time_ms' => round($queries->sum('time'), 2),
            'avg_time_ms' => round($queries->avg('time'), 2),
            'slowest_query_ms' => round($queries->max('time'), 2),
            'slow_queries_count' => $queries->filter(fn($q) => $q['time'] > self::SLOW_QUERY_THRESHOLD_MS)->count(),
        ];
    }

    /**
     * Identify potential N+1 query problems.
     * Looks for repeated similar queries.
     *
     * @param int $threshold Number of similar queries to consider as N+1
     * @return Collection
     */
    public function detectNPlusOne(int $threshold = 5): Collection
    {
        return collect($this->queries)
            ->groupBy(fn($query) => $this->normalizeQuery($query['sql']))
            ->filter(fn($group) => $group->count() >= $threshold)
            ->map(fn($group) => [
                'count' => $group->count(),
                'total_time_ms' => round($group->sum('time'), 2),
                'sample_sql' => $group->first()['sql'],
            ])
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Normalize a query for comparison (remove specific values).
     *
     * @param string $sql
     * @return string
     */
    protected function normalizeQuery(string $sql): string
    {
        // Remove specific numeric values
        $normalized = preg_replace('/\b\d+\b/', '?', $sql);

        // Remove specific string values in quotes
        $normalized = preg_replace('/\'[^\']*\'/', '?', $normalized);

        // Remove extra whitespace
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
    }

    /**
     * Get indexes for a table.
     *
     * @param string $table
     * @return array
     */
    public function getTableIndexes(string $table): array
    {
        $driver = DB::connection()->getDriverName();

        try {
            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    $indexes = DB::select("SHOW INDEX FROM {$table}");
                    return collect($indexes)->map(fn($idx) => [
                        'name' => $idx->Key_name,
                        'column' => $idx->Column_name,
                        'unique' => !$idx->Non_unique,
                    ])->toArray();

                case 'pgsql':
                    $indexes = DB::select("
                        SELECT indexname, indexdef 
                        FROM pg_indexes 
                        WHERE tablename = ?
                    ", [$table]);
                    return collect($indexes)->map(fn($idx) => [
                        'name' => $idx->indexname,
                        'definition' => $idx->indexdef,
                    ])->toArray();

                case 'sqlite':
                    $indexes = DB::select("PRAGMA index_list({$table})");
                    return collect($indexes)->map(fn($idx) => [
                        'name' => $idx->name,
                        'unique' => (bool) $idx->unique,
                    ])->toArray();

                default:
                    return [];
            }
        } catch (\Exception $e) {
            Log::warning('DatabaseOptimizationService: Failed to get indexes', [
                'table' => $table,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Optimize a table (MySQL/MariaDB only).
     *
     * @param string $table
     * @return bool
     */
    public function optimizeTable(string $table): bool
    {
        $driver = DB::connection()->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'])) {
            Log::info('DatabaseOptimizationService: OPTIMIZE TABLE only supported on MySQL/MariaDB');
            return false;
        }

        try {
            DB::statement("OPTIMIZE TABLE {$table}");
            Log::info("DatabaseOptimizationService: Optimized table {$table}");
            return true;
        } catch (\Exception $e) {
            Log::error('DatabaseOptimizationService: Failed to optimize table', [
                'table' => $table,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get a summary report of query analysis.
     *
     * @return array
     */
    public function getReport(): array
    {
        return [
            'statistics' => $this->getStatistics(),
            'slow_queries' => $this->analyzeSlowQueries()->take(10)->toArray(),
            'potential_n_plus_one' => $this->detectNPlusOne()->take(5)->toArray(),
        ];
    }
}
