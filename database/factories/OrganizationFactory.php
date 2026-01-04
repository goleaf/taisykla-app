<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement(['business', 'consumer']),
            'status' => 'active',
            'primary_contact_name' => fake()->name(),
            'primary_contact_email' => fake()->unique()->companyEmail(),
            'primary_contact_phone' => fake()->phoneNumber(),
            'billing_email' => fake()->companyEmail(),
            'billing_address' => fake()->address(),
            'notes' => fake()->sentence(),
        ];
    }
}
