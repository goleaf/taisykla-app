<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFeedbackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_order_id' => \App\Models\WorkOrder::factory(),
            'user_id' => \App\Models\User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'professionalism_rating' => fake()->numberBetween(1, 5),
            'knowledge_rating' => fake()->numberBetween(1, 5),
            'communication_rating' => fake()->numberBetween(1, 5),
            'timeliness_rating' => fake()->numberBetween(1, 5),
            'quality_rating' => fake()->numberBetween(1, 5),
            'would_recommend' => fake()->boolean(80),
            'comments' => fake()->paragraph(),
        ];
    }
}
