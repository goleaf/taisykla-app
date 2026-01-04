<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => \App\Models\Invoice::factory(),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'method' => fake()->randomElement(['credit_card', 'bank_transfer', 'check', 'cash']),
            'status' => 'completed',
            'reference' => fake()->bothify('REF-####'),
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
