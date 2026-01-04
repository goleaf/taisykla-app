<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KnowledgeCategory>
 */
class KnowledgeCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => 'folder',
            'sort_order' => fake()->numberBetween(0, 100),
            'parent_id' => null,
        ];
    }
}
2