<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PartCategory;
use App\Models\Part;
use App\Models\InventoryLocation;
use App\Models\InventoryItem;
use App\Models\EquipmentCategory;
use App\Models\Equipment;
use App\Models\Organization;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Part Categories
        $categories = [
            'Consumables' => ['Toner', 'Ink', 'Paper', 'Cleaning Supplies'],
            'Replacement Parts' => ['Hard Drives', 'RAM', 'Batteries', 'Screens', 'Keyboards'],
            'Cables' => ['Ethernet', 'HDMI', 'USB', 'Power Cords'],
            'Accessories' => ['Mice', 'Keyboards', 'Headsets', 'Webcams'],
        ];

        $catIds = [];
        foreach ($categories as $parent => $children) {
            $parentCat = PartCategory::firstOrCreate(['name' => $parent]);
            $catIds[] = $parentCat->id;
            foreach ($children as $child) {
                $childCat = PartCategory::firstOrCreate([
                    'name' => $child,
                    'parent_id' => $parentCat->id
                ]);
                $catIds[] = $childCat->id;
            }
        }

        // 2. Locations
        $mainWarehouse = InventoryLocation::firstOrCreate(
            ['name' => 'Main Warehouse'],
            ['address' => '123 Supply Depot, Tech City']
        );
        $van1 = InventoryLocation::firstOrCreate(
            ['name' => 'Van #101 (Alex)'],
            ['address' => 'Mobile']
        );
        $van2 = InventoryLocation::firstOrCreate(
            ['name' => 'Van #102 (Sam)'],
            ['address' => 'Mobile']
        );

        // 3. Parts (More realistic generation)
        // We'll generate 100 parts
        Part::factory(100)->create()->each(function ($part) use ($catIds, $mainWarehouse, $van1, $van2) {
            // Assign random category
            $part->update(['part_category_id' => fake()->randomElement($catIds)]);

            // Stock in warehouse (High qty)
            InventoryItem::create([
                'part_id' => $part->id,
                'location_id' => $mainWarehouse->id,
                'quantity' => fake()->numberBetween(10, 500),
                'reserved_quantity' => 0,
            ]);

            // Stock in vans (Lower qty)
            if (fake()->boolean(60)) {
                InventoryItem::create([
                    'part_id' => $part->id,
                    'location_id' => $van1->id,
                    'quantity' => fake()->numberBetween(0, 10),
                    'reserved_quantity' => 0,
                ]);
            }
            if (fake()->boolean(60)) {
                InventoryItem::create([
                    'part_id' => $part->id,
                    'location_id' => $van2->id,
                    'quantity' => fake()->numberBetween(0, 10),
                    'reserved_quantity' => 0,
                ]);
            }
        });

        // 4. Equipment Categories
        $equipCats = ['Printers', 'Laptops', 'Desktops', 'Servers', 'Networking', 'Mobile Devices'];
        $equipCatIds = [];
        foreach ($equipCats as $ec) {
            $equipCatIds[] = EquipmentCategory::firstOrCreate(['name' => $ec])->id;
        }

        // 5. Generate Equipment for Organizations
        $organizations = Organization::all();
        foreach ($organizations as $org) {
            // Give each org 5-15 pieces of equipment
            Equipment::factory(fake()->numberBetween(5, 15))->create([
                'organization_id' => $org->id,
                'equipment_category_id' => fake()->randomElement($equipCatIds),
            ]);
        }
    }
}