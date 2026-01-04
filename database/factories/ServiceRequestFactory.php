<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Organization;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Organization::factory(),
            'equipment_id' => Equipment::factory(),
            'technician_id' => User::factory(),
            'priority' => $this->faker->randomElement([
                ServiceRequest::PRIORITY_LOW,
                ServiceRequest::PRIORITY_MEDIUM,
                ServiceRequest::PRIORITY_HIGH,
                ServiceRequest::PRIORITY_URGENT,
            ]),
            'status' => ServiceRequest::STATUS_PENDING,
            'description' => $this->faker->paragraph(),
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'estimated_cost' => $this->faker->randomFloat(2, 50, 500),
            'approval_status' => ServiceRequest::APPROVAL_PENDING,
        ];
    }

    /**
     * Indicate that the service request is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => ServiceRequest::PRIORITY_HIGH,
        ]);
    }

    /**
     * Indicate that the service request is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => ServiceRequest::STATUS_COMPLETED,
            'completed_at' => now(),
            'actual_cost' => $this->faker->randomFloat(2, 50, 500),
        ]);
    }
}
