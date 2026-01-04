<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 2000);
        $tax = round($subtotal * 0.1, 2);

        return [
            'organization_id' => \App\Models\Organization::factory(),
            'work_order_id' => \App\Models\WorkOrder::factory(),
            'quote_number' => fake()->unique()->bothify('Q-#####'),
            'status' => fake()->randomElement(['draft', 'sent', 'approved', 'rejected']),
            'valid_until' => fake()->dateTimeBetween('+1 week', '+1 month'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'notes' => fake()->sentence(),
        ];
    }
}
