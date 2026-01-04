<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Part>
 */
class PartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 5, 500);
        return [
            'sku' => fake()->unique()->bothify('SKU-#####'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'manufacturer' => fake()->company(),
            'unit_cost' => $cost,
            'unit_price' => $cost * 1.5,
            'reorder_level' => fake()->numberBetween(1, 10),
            'reorder_quantity' => fake()->numberBetween(10, 50),
            'is_active' => true,
        ];
    }
}
