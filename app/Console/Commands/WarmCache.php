<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

/**
 * Artisan command to pre-populate critical caches.
 * Useful to run during deployment to ensure cache is warm.
 */
class WarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm {--clear : Clear existing cache before warming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-populate critical application caches';

    /**
     * Execute the console command.
     */
    public function handle(CacheService $cacheService): int
    {
        $this->info('Starting cache warming...');

        // Optionally clear cache first
        if ($this->option('clear')) {
            $this->warn('Clearing existing cache...');
            $this->call('cache:clear');
        }

        // Warm caches
        $this->info('Warming equipment types cache...');
        $cacheService->rememberEquipmentTypes();
        $this->line('  ✓ Equipment types cached (forever)');

        $this->info('Warming dashboard statistics...');
        $cacheService->rememberDashboardStatistics();
        $this->line('  ✓ Dashboard statistics cached (5 minutes)');

        // Display cache info
        $stats = $cacheService->getStatistics();
        $this->newLine();
        $this->info('Cache Statistics:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Driver', $stats['driver']],
                ['Supports Tags', $stats['supports_tags'] ? 'Yes' : 'No'],
                ['Prefix', $stats['prefix'] ?: '(none)'],
            ]
        );

        $this->newLine();
        $this->info('✓ Cache warming completed successfully!');

        return Command::SUCCESS;
    }
}
