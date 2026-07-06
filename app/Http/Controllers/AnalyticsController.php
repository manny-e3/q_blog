<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Dashboard summary metrics.
     */
    public function dashboard()
    {
        $metrics = $this->analyticsService->getDashboardMetrics();
        return response()->json($metrics);
    }

    /**
     * Article analytics (date range).
     */
    public function articles(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
        ]);

        $analytics = $this->analyticsService->getArticlesAnalytics($request->startDate, $request->endDate);

        return response()->json($analytics);
    }

    /**
     * Top performing articles.
     */
    public function topArticles()
    {
        $articles = $this->analyticsService->getTopArticles();
        return response()->json($articles);
    }

    /**
     * Top performing categories.
     */
    public function topCategories()
    {
        $categories = $this->analyticsService->getTopCategories();
        return response()->json($categories);
    }

    /**
     * Top performing authors.
     */
    public function topAuthors()
    {
        $authors = $this->analyticsService->getTopAuthors();
        return response()->json($authors);
    }

    /**
     * Traffic source breakdown.
     */
    public function trafficSources()
    {
        $traffic = $this->analyticsService->getTrafficSources();
        return response()->json($traffic);
    }

    /**
     * Reading time metrics.
     */
    public function readingTime()
    {
        $metrics = $this->analyticsService->getReadingTimeMetrics();
        return response()->json($metrics);
    }

    /**
     * Share metrics.
     */
    public function shares()
    {
        $shares = $this->analyticsService->getShareMetrics();
        return response()->json($shares);
    }

    /**
     * Export analytics as CSV.
     */
    public function exportCsv()
    {
        $callback = $this->analyticsService->exportCsvCallback();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=q_blog_analytics.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export analytics as PDF.
     */
    public function exportPdf()
    {
        $pdfContent = $this->analyticsService->exportPdfContent();

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="q_blog_analytics.pdf"');
    }
}
