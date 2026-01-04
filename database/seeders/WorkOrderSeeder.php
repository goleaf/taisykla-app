<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have basics
        $organizations = \App\Models\Organization::with('equipment')->get();
        if ($organizations->isEmpty()) {
            return;
        }

        $technicians = \App\Models\User::role(\App\Support\RoleCatalog::TECHNICIAN)->get();

        // Create categories
        $categories = ['Repair', 'Maintenance', 'Installation', 'Inspection'];
        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[] = \App\Models\WorkOrderCategory::firstOrCreate(['name' => $cat])->id;
        }

        // Create WOs with factory
        \App\Models\WorkOrder::factory(50)->make()->each(function ($wo) use ($organizations, $technicians, $catIds) {
            // Pick rand org
            $org = $organizations->random();
            $equipment = $org->equipment->isNotEmpty() ? $org->equipment->random() : null;
            $tech = $technicians->isNotEmpty() ? $technicians->random() : null;

            $wo->organization_id = $org->id;
            $wo->equipment_id = $equipment?->id;
            $wo->category_id = fake()->randomElement($catIds);

            // If assigned status, assign a tech
            if (in_array($wo->status, ['assigned', 'in_progress', 'completed'])) {
                $wo->assigned_to_user_id = $tech?->id;
                $wo->assigned_at = fake()->dateTimeBetween('-1 week', 'now');
            }

            $wo->save();
        });
    }
}
