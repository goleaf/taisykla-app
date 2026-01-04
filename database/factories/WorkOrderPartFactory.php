<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderPartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_order_id' => \App\Models\WorkOrder::factory(),
            'part_id' => \App\Models\Part::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_cost' => fake()->randomFloat(2, 10, 100),
            'unit_price' => fake()->randomFloat(2, 20, 200),
        ];
    }
}
