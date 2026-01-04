<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');
        return [
            'work_order_id' => \App\Models\WorkOrder::factory(),
            'assigned_to_user_id' => \App\Models\User::factory(),
            'scheduled_start_at' => $start,
            'scheduled_end_at' => (clone $start)->modify('+2 hours'),
            'status' => fake()->randomElement(['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled']),
            'notes' => fake()->sentence(),
        ];
    }
}
