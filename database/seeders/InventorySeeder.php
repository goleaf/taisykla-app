<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Part Categories
        $categories = ['Consumables', 'Replacement Parts', 'Cables', 'Accessories'];
        foreach ($categories as $cat) {
            \App\Models\PartCategory::firstOrCreate(['name' => $cat]);
        }

        // 2. Parts
        $parts = \App\Models\Part::factory(20)->create();

        // 3. Locations
        $mainWarehouse = \App\Models\InventoryLocation::firstOrCreate(
            ['name' => 'Main Warehouse'],
            ['address' => '123 Supply Depot']
        );
        $van1 = \App\Models\InventoryLocation::firstOrCreate(
            ['name' => 'Van #101'],
            ['address' => 'Mobile']
        );

        // 4. Initial Stock
        foreach ($parts as $part) {
            // Stock in warehouse
            \App\Models\InventoryItem::create([
                'part_id' => $part->id,
                'location_id' => $mainWarehouse->id,
                'quantity' => fake()->numberBetween(10, 100),
                'reserved_quantity' => 0,
            ]);

            // Stock in van
            \App\Models\InventoryItem::create([
                'part_id' => $part->id,
                'location_id' => $van1->id,
                'quantity' => fake()->numberBetween(0, 5),
                'reserved_quantity' => 0,
            ]);
        }

        // 5. Equipment Categories
        $equipCats = ['Printers', 'Laptops', 'Servers', 'Networking'];
        foreach ($equipCats as $ec) {
            \App\Models\EquipmentCategory::firstOrCreate(['name' => $ec]);
        }

        // 6. Generate Equipment for Organizations
        $organizations = \App\Models\Organization::all();
        foreach ($organizations as $org) {
            \App\Models\Equipment::factory(fake()->numberBetween(1, 5))->create([
                'organization_id' => $org->id,
                'equipment_category_id' => \App\Models\EquipmentCategory::inRandomOrder()->first()->id,
            ]);
        }
    }
}
