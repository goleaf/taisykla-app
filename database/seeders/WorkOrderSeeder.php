<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\WorkOrder;
use App\Models\WorkOrderCategory;
use App\Models\User;
use App\Support\RoleCatalog;

class WorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have basics
        $organizations = Organization::with('equipment')->get();
        if ($organizations->isEmpty()) {
            return;
        }

        $technicians = User::role(RoleCatalog::TECHNICIAN)->get();

        // Create categories
        $categories = ['Repair', 'Maintenance', 'Installation', 'Inspection', 'Consultation', 'Emergency'];
        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[] = WorkOrderCategory::firstOrCreate(['name' => $cat])->id;
        }

        // Create WOs with factory
        // Generate 100 Work Orders for better density
        WorkOrder::factory(100)->make()->each(function ($wo) use ($organizations, $technicians, $catIds) {
            // Pick rand org
            $org = $organizations->random();
            $equipment = $org->equipment->isNotEmpty() ? $org->equipment->random() : null;
            $tech = $technicians->isNotEmpty() ? $technicians->random() : null;

            $wo->organization_id = $org->id;
            $wo->equipment_id = $equipment?->id;
            $wo->category_id = fake()->randomElement($catIds);

            // Assign requester (primary contact of org)
            $requester = $org->users->first();
            if ($requester) {
                $wo->requested_by_user_id = $requester->id;
            }

            // If assigned status, assign a tech
            if (in_array($wo->status, ['assigned', 'in_progress', 'completed', 'on_hold'])) {
                $wo->assigned_to_user_id = $tech?->id;
                $wo->assigned_at = fake()->dateTimeBetween('-1 month', 'now');
            }

            // Logic for dates based on status
            if ($wo->status === 'completed') {
                $wo->started_at = $wo->assigned_at->addHours(rand(1, 48));
                $wo->completed_at = $wo->started_at->addMinutes(rand(30, 240));
            } elseif ($wo->status === 'in_progress') {
                $wo->started_at = $wo->assigned_at->addHours(rand(1, 24));
            }

            $wo->save();
        });
    }
}