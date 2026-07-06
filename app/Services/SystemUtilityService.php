<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SystemUtilityService
{
    /**
     * Generate SEO-friendly slug.
     */
    public function generateSlug(string $title): string
    {
        return Str::slug($title);
    }

    /**
     * Calculate reading time.
     */
    public function calculateReadingTime(?string $content, ?int $articleId = null): array
    {
        if ($content === null && $articleId !== null) {
            $article = Article::find($articleId);
            if ($article) {
                $content = $article->content;
            }
        }

        $content = $content ?? '';
        $wordCount = str_word_count(strip_tags($content));
        $readingTimeMinutes = max(1, (int) ceil($wordCount / 200));

        return [
            'reading_time_minutes' => $readingTimeMinutes,
            'word_count' => $wordCount
        ];
    }

    /**
     * Check if database connection is up.
     */
    public function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
