<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
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
            'invoice_number' => fake()->unique()->bothify('INV-#####'),
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'issued_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'notes' => fake()->sentence(),
        ];
    }
}
