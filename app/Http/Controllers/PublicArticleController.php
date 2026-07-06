<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;

class PublicArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * Featured & latest articles.
     */
    public function featured()
    {
        $data = $this->articleService->getFeaturedAndLatest();
        return response()->json($data);
    }

    /**
     * Get articles (filterable, paginated).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'author', 'tag']);
        $sort = $request->input('sort', 'latest');
        $limit = $request->input('limit', 12);

        $articles = $this->articleService->getPublicArticles($filters, $sort, $limit);

        return response()->json($articles);
    }

    /**
     * Full-text search across articles (Searches: Title, Content, Author name, Tags).
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('q');

        if (!$searchTerm) {
            return response()->json(['message' => 'Search query term required.'], 400);
        }

        $articles = $this->articleService->searchArticles($searchTerm);

        return response()->json($articles);
    }

    /**
     * Get article details by slug.
     */
    public function showBySlug($slug)
    {
        $article = $this->articleService->findBySlug($slug);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        return response()->json($article);
    }

    /**
     * Get related articles.
     */
    public function related($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $related = $this->articleService->getRelatedArticles($article);

        return response()->json($related);
    }

    /**
     * Download article as PDF.
     */
    public function downloadPdf($id)
    {
        $article = Article::where('id', $id)
            ->where('status', 'published')
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $pdfContent = $this->articleService->generateArticlePdf($article);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $article->slug . '.pdf"');
    }

    /**
     * Track article view.
     */
    public function trackView($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $viewsCount = $this->articleService->trackView($article);

        return response()->json([
            'message' => 'View tracked successfully.',
            'views_count' => $viewsCount
        ]);
    }

    /**
     * Track social share event.
     */
    public function trackShare(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $validated = $request->validate([
            'platform' => 'required|string|in:LinkedIn,X,Facebook,Instagram,Email,Copy Link'
        ]);

        $sharesCount = $this->articleService->trackShare($article, $validated['platform']);

        return response()->json([
            'message' => 'Share tracked successfully on ' . $validated['platform'],
            'shares_count' => $sharesCount
        ]);
    }
}
