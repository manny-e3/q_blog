<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Raw user IDs from external service
        $adminId = 1;
        $authorId = 2;

        // 2. Create Categories
        $sustainability = \App\Models\Category::create([
            'name' => 'Sustainability',
            'slug' => 'sustainability',
            'status' => 'active',
        ]);

        $bonds = \App\Models\Category::create([
            'name' => 'Bond Market',
            'slug' => 'bond-market',
            'status' => 'active',
        ]);

        $finance = \App\Models\Category::create([
            'name' => 'Finance',
            'slug' => 'finance',
            'status' => 'active',
        ]);

        // 3. Create Tags
        $tagBonds = \App\Models\Tag::create(['name' => 'bonds', 'slug' => 'bonds']);
        $tagGreen = \App\Models\Tag::create(['name' => 'green', 'slug' => 'green']);
        $tagNigeria = \App\Models\Tag::create(['name' => 'nigeria', 'slug' => 'nigeria']);

        // 4. Create Articles
        // Featured & Published
        $article1 = \App\Models\Article::create([
            'title' => "Understanding Nigeria's Bond Market",
            'slug' => 'understanding-nigerias-bond-market',
            'content' => 'This is a comprehensive guide to understanding Nigeria\'s Bond Market dynamics, yield curves, and government issuance policies.',
            'summary' => 'A guide to Nigeria\'s bond market.',
            'status' => 'published',
            'is_featured' => true,
            'inputter_id' => $authorId,
            'authoriser_id' => $adminId,
            'category_id' => $bonds->id,
            'views_count' => 120,
            'shares_count' => 15,
        ]);
        $article1->tags()->attach([$tagBonds->id, $tagNigeria->id]);

        // Published Sustainability article
        $article2 = \App\Models\Article::create([
            'title' => 'Green Bonds and Sustainability Initiatives',
            'slug' => 'green-bonds-and-sustainability-initiatives',
            'content' => 'Green bonds are growing rapidly in sub-Saharan Africa, supporting solar energy and reforestation projects.',
            'summary' => 'An overview of green bonds growth.',
            'status' => 'published',
            'is_featured' => false,
            'inputter_id' => $authorId,
            'authoriser_id' => $adminId,
            'category_id' => $sustainability->id,
            'views_count' => 85,
            'shares_count' => 22,
        ]);
        $article2->tags()->attach([$tagBonds->id, $tagGreen->id]);

        // Draft
        $article3 = \App\Models\Article::create([
            'title' => 'Draft Article on Treasury Bills',
            'slug' => 'draft-article-on-treasury-bills',
            'content' => 'Draft content about treasury bills pricing and mechanics.',
            'summary' => 'Draft summary.',
            'status' => 'draft',
            'is_featured' => false,
            'inputter_id' => $authorId,
            'category_id' => $finance->id,
        ]);

        // Pending
        $article4 = \App\Models\Article::create([
            'title' => 'Review of FMDQ Commercial Papers',
            'slug' => 'review-of-fmdq-commercial-papers',
            'content' => 'Commercial papers offer short-term financing alternatives for corporates. Here is how FMDQ registers them.',
            'summary' => 'Overview of FMDQ commercial papers.',
            'status' => 'pending',
            'is_featured' => false,
            'inputter_id' => $authorId,
            'category_id' => $finance->id,
        ]);
    }
}
