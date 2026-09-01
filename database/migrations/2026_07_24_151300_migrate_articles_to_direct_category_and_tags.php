<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add category_ids and tag_ids JSON columns to articles
        Schema::table('articles', function (Blueprint $table) {
            $table->json('category_ids')->nullable()->after('authoriser_id');
            $table->json('tag_ids')->nullable()->after('category_ids');
        });

        // 2. Migrate existing category data
        if (Schema::hasColumn('articles', 'category_id')) {
            $articles = DB::table('articles')->get();
            foreach ($articles as $article) {
                if ($article->category_id) {
                    DB::table('articles')
                        ->where('id', $article->id)
                        ->update(['category_ids' => json_encode([(int)$article->category_id])]);
                }
            }
        }

        // 3. Migrate existing tag data from article_tag
        if (Schema::hasTable('article_tag')) {
            $articleTags = DB::table('article_tag')->get();
            $groupedTags = [];
            foreach ($articleTags as $at) {
                $groupedTags[$at->article_id][] = (int)$at->tag_id;
            }
            foreach ($groupedTags as $articleId => $tagIds) {
                DB::table('articles')
                    ->where('id', $articleId)
                    ->update(['tag_ids' => json_encode($tagIds)]);
            }
        }

        // 4. Drop single category_id column and foreign key constraint
        Schema::table('articles', function (Blueprint $table) {
            // Drop foreign key safely
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        // 5. Drop article_tag table
        Schema::dropIfExists('article_tag');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create category_id column
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
        });

        // Restore category_id values
        $articles = DB::table('articles')->get();
        foreach ($articles as $article) {
            if ($article->category_ids) {
                $ids = json_decode($article->category_ids, true);
                if (!empty($ids)) {
                    DB::table('articles')
                        ->where('id', $article->id)
                        ->update(['category_id' => $ids[0]]);
                }
            }
        }

        // Re-create article_tag table
        Schema::create('article_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->unique(['article_id', 'tag_id']);
        });

        // Restore tag values from tag_ids to article_tag
        $articles = DB::table('articles')->get();
        foreach ($articles as $article) {
            if ($article->tag_ids) {
                $tagIds = json_decode($article->tag_ids, true);
                if (is_array($tagIds)) {
                    foreach ($tagIds as $tagId) {
                        DB::table('article_tag')->insert([
                            'article_id' => $article->id,
                            'tag_id' => $tagId
                        ]);
                    }
                }
            }
        }

        // Drop array columns
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['category_ids', 'tag_ids']);
        });
    }
};
