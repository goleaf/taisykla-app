<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckEquipmentWarranties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment:check-warranties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for equipment with expiring warranties and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking equipment warranties...');

        // Find equipment expiring in the next 30 days
        $equipment = \App\Models\Equipment::query()
            ->whereDate('warranty_expiry', '>', now())
            ->whereDate('warranty_expiry', '<=', now()->addDays(30))
            ->get();

        $count = 0;
        foreach ($equipment as $item) {
            // Logic to prevent duplicate notifications could be added here or in the method
            $item->checkWarrantyExpiry();
            $count++;
        }

        $this->info("Checked {$equipment->count()} items. Notifications triggered for {$count}.");
    }
}
