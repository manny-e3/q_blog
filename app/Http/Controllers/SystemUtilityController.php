<?php

namespace App\Http\Controllers;

use App\Services\SystemUtilityService;
use Illuminate\Http\Request;

class SystemUtilityController extends Controller
{
    protected $systemUtilityService;

    public function __construct(SystemUtilityService $systemUtilityService)
    {
        $this->systemUtilityService = $systemUtilityService;
    }

    /**
     * Generate SEO-friendly slug from title.
     */
    public function generateSlug(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $slug = $this->systemUtilityService->generateSlug($validated['title']);

        return response()->json([
            'slug' => $slug
        ]);
    }

    /**
     * Calculate article reading time.
     */
    public function readingTime(Request $request)
    {
        $validated = $request->validate([
            'content' => 'sometimes|string',
            'article_id' => 'sometimes|integer',
            'articleId' => 'sometimes|integer',
        ]);

        $content = $validated['content'] ?? null;
        $articleId = $validated['article_id'] ?? $validated['articleId'] ?? null;

        $metrics = $this->systemUtilityService->calculateReadingTime($content, $articleId);

        return response()->json([
            'reading_time_minutes' => $metrics['reading_time_minutes'],
            'word_count' => $metrics['word_count']
        ]);
    }

    /**
     * Health check.
     */
    public function health()
    {
        $dbConnected = $this->systemUtilityService->checkDatabaseConnection();

        return response()->json([
            'status' => 'UP',
            'database' => $dbConnected ? 'connected' : 'disconnected',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
