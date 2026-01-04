<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cats = ['Hardware', 'Software', 'Networking', 'Security'];
        $catModels = [];
        foreach ($cats as $name) {
            $catModels[] = \App\Models\KnowledgeCategory::firstOrCreate(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name)]);
        }

        $admin = \App\Models\User::role(\App\Support\RoleCatalog::ADMIN)->first();

        // Create articles using factory
        \App\Models\KnowledgeArticle::factory(20)->make()->each(function ($article) use ($catModels, $admin) {
            $article->category_id = fake()->randomElement($catModels)->id;
            $article->created_by_user_id = $admin?->id ?? 1;
            $article->save();
        });
    }
}
