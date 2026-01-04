<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => \App\Models\Organization::factory(),
            'equipment_id' => \App\Models\Equipment::factory(),
            'requested_by_user_id' => \App\Models\User::factory(),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'status' => fake()->randomElement(['open', 'assigned', 'in_progress', 'completed', 'closed']),
            'requested_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'location_name' => fake()->city(),
            'location_address' => fake()->address(),
            'total_cost' => fake()->randomFloat(2, 50, 1000),
        ];
    }
}
