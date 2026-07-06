<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CmsArticleController extends Controller
{
    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * Get single article for CMS editing.
     */
    public function show($id)
    {
        $article = $this->articleService->findArticleById((int)$id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $user = Auth::user();
        if (!$this->articleService->authorizeUser($user, $article)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($article);
    }

    /**
     * Create new article.
     */
    public function store(Request $request)
    {
      
        // Ensure JSON body is parsed regardless of Content-Type header
        if ($request->isJson() || $request->getContent()) {
            $json = $request->json()->all();
            if (!empty($json)) {
                $request->merge($json);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id', 
            'is_featured' => 'nullable|boolean',
            'inputter_id' => 'nullable|integer',
            'authoriser_id' => 'nullable|integer',
            'status' => 'nullable|string|in:draft,pending,published',
        ]); 

         $inputterId = $request->input('inputter_id') ?? $request->input('user_id') ?? (Auth::user() ? Auth::user()->id : null);

        if (!$inputterId) {
            return response()->json(['message' => 'Inputter ID is required.'], 400);
        }

        if (!empty($validated['authoriser_id'])) {
             $userService = resolve(\App\Services\ExternalUserService::class);
        
            $authoriser = $userService->getUserById((int)$validated['authoriser_id']);
            if (!$authoriser) {
                return response()->json(['message' => 'Invalid Authoriser ID.'], 400);
            }
            $roleName = '';
            if (isset($authoriser['role'])) {
                if (is_array($authoriser['role'])) {
                    $roleName = $authoriser['role']['name'] ?? '';
                } else {
                    $roleName = $authoriser['role'];
                }
            }
         
        }

        $article = $this->articleService->storeArticle((int)$inputterId, $validated);

        return response()->json($article, 201);
    }

    /**
     * Update article.
     */
    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $user = Auth::user();
        if (!$this->articleService->authorizeUser($user, $article)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'summary' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|string|in:draft,pending,published',
        ]);

        $updatedArticle = $this->articleService->updateArticle($article, $validated);

        return response()->json($updatedArticle);
    }

    /**
     * Delete article.
     */
    public function destroy($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $user = Auth::user();
        if (!$this->articleService->authorizeUser($user, $article)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $this->articleService->deleteArticle($article);

        return response()->json(['message' => 'Article deleted successfully.']);
    }

    /**
     * Save article as draft.
     */
    public function saveDraft($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $user = Auth::user();
        if (!$this->articleService->authorizeUser($user, $article)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $updatedArticle = $this->articleService->saveAsDraft($article);

        return response()->json([
            'message' => 'Article status updated to draft.',
            'article' => $updatedArticle
        ]);
    }

    /**
     * Preview rendered HTML.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $html = $this->articleService->renderMarkdown($validated['content']);

        return response()->json([
            'html' => $html
        ]);
    }

    /**
     * Publish / Submit for approval.
     */
    public function publish($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $user = Auth::user();
        $result = $this->articleService->publishArticle($article, $user);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 403);
        }

        return response()->json([
            'message' => $result['message'],
            'article' => $result['article']
        ]);
    }

    /**
     * Unpublish article.
     */
    public function unpublish($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $user = Auth::user();
        if (!$this->articleService->authorizeUser($user, $article)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $updatedArticle = $this->articleService->unpublishArticle($article);

        return response()->json([
            'message' => 'Article unpublished and status reset to draft.',
            'article' => $updatedArticle
        ]);
    }
}
