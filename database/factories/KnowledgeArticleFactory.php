<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KnowledgeArticle>
 */
class KnowledgeArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['draft', 'published', 'archived']);
        $publishedAt = $status === 'published' ? fake()->dateTimeBetween('-1 year', 'now') : null;

        return [
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'summary' => fake()->paragraph(),
            'content' => fake()->markdown(),
            'status' => $status,
            'is_published' => $status === 'published',
            'published_at' => $publishedAt,
            'category_id' => \App\Models\KnowledgeCategory::factory(), // Assuming we will update this or use firstOrCreate
            'visibility' => 'public',
            'author_name' => fake()->name(),
            'created_by_user_id' => \App\Models\User::factory(),
        ];
    }
}
