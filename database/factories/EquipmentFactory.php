<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $manufacturers = ['HP', 'Dell', 'Canon', 'Lenovo', 'Apple', 'Brother', 'Cisco'];
        $types = ['Printer', 'Laptop', 'Desktop', 'Server', 'Router', 'Switch'];

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement($types),
            'manufacturer' => fake()->randomElement($manufacturers),
            'model' => fake()->bothify('MN-####-??'),
            'serial_number' => fake()->unique()->bothify('SN-########'),
            'status' => fake()->randomElement(['operational', 'needs_attention', 'in_repair']),
            'location_name' => fake()->word() . ' Room',
            'location_address' => fake()->address(),
            'purchase_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'purchase_price' => fake()->randomFloat(2, 500, 5000),
            'notes' => fake()->sentence(),
        ];
    }
}
