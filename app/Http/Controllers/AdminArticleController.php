<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Http\Request;

class AdminArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function published()
    {
        $articles = $this->articleService->getAdminArticlesByStatus('published');
        return response()->json($articles);
    }

    public function unpublished()
    {
        // Draft or rejected
        $articles = $this->articleService->getAdminArticlesByStatus(['draft', 'rejected']);
        return response()->json($articles);
    }

    public function pending()
    {
        $articles = $this->articleService->getAdminArticlesByStatus('pending');
        return response()->json($articles);
    }

    public function rejected()
    {
        $articles = $this->articleService->getAdminArticlesByStatus('rejected');
        return response()->json($articles);
    }
}
