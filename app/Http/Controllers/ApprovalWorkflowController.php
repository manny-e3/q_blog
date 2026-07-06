<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalWorkflowController extends Controller
{
    protected $approvalWorkflowService;

    public function __construct(ApprovalWorkflowService $approvalWorkflowService)
    {
        $this->approvalWorkflowService = $approvalWorkflowService;
    }

    /**
     * Approve a pending article.
     */
    public function approve($articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        if ($article->status !== 'pending') {
            return response()->json(['message' => 'Article is not pending approval.'], 400);
        }

        $user = Auth::user();
        $updatedArticle = $this->approvalWorkflowService->approve($article, $user);

        return response()->json([
            'message' => 'Article approved and published successfully.',
            'article' => $updatedArticle
        ]);
    }

    /**
     * Reject a pending article.
     */
    public function reject(Request $request, $articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        if ($article->status !== 'pending') {
            return response()->json(['message' => 'Article is not pending approval.'], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $user = Auth::user();
        $updatedArticle = $this->approvalWorkflowService->reject($article, $user, $validated['reason']);

        return response()->json([
            'message' => 'Article rejected successfully.',
            'article' => $updatedArticle
        ]);
    }

    /**
     * Get approval history.
     */
    public function history($articleId)
    {
        $article = Article::find($articleId);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $history = $this->approvalWorkflowService->getHistory($article);

        return response()->json($history);
    }

    /**
     * Count of pending approvals.
     */
    public function pendingCount()
    {
        $count = $this->approvalWorkflowService->getPendingCount();

        return response()->json([
            'pending_count' => $count
        ]);
    }
}
