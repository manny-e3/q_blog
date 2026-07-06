<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    private function getMyArticlesByStatus($status)
    {
        $user = Auth::user();
        $articles = $this->articleService->getUserArticlesByStatus($user, $status);

        return response()->json($articles);
    }

    public function published()
    {
        return $this->getMyArticlesByStatus('published');
    }

    public function drafts()
    {
        return $this->getMyArticlesByStatus('draft');
    }

    public function pending()
    {
        return $this->getMyArticlesByStatus('pending');
    }

    public function rejected()
    {
        return $this->getMyArticlesByStatus('rejected');
    }
}
