<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
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
            'work_order_id' => \App\Models\WorkOrder::factory(),
            'submitted_by_user_id' => \App\Models\User::factory(),
            'assigned_to_user_id' => \App\Models\User::factory(),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'standard', 'high', 'critical']),
            'status' => fake()->randomElement(['open', 'in_progress', 'resolved', 'closed']),
            'resolution_notes' => fake()->optional()->paragraph(),
            'resolved_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
