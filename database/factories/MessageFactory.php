<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'thread_id' => \App\Models\MessageThread::factory(),
            'user_id' => \App\Models\User::factory(),
            'body' => fake()->paragraph(),
            'created_at' => now(),
        ];
    }
}