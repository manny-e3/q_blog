<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ApprovalHistory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use App\Services\MediaService;

class ArticleService
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    /**
     * Get articles by status for admin.
     */
    public function getAdminArticlesByStatus(array|string $status)
    {
        $query = Article::query();

        if (is_array($status)) {
            $query->whereIn('status', $status);
        } else {
            $query->where('status', $status);
        }

        return $this->enrichArticles($query->with(['category', 'tags'])->get());
    }

    /**
     * Get my articles by status.
     */
    public function getUserArticlesByStatus(Authenticatable $user, string $status)
    {
        return $this->enrichArticles(Article::where('inputter_id', $user->id)
            ->where('status', $status)
            ->with(['category', 'tags'])
            ->get());
    }

    /**
     * Store new article.
     */
    public function storeArticle(int $inputterId, array $data): Article
    {
        $slug = Str::slug($data['title']);
        $baseSlug = $slug;
        $counter = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $status = 'pending'; // Default status is pending (needs approval)
        
        $userService = resolve(\App\Services\ExternalUserService::class);
        $user = $userService->getUserById($inputterId);
        // if ($user) {
        //     $roleName = '';
        //     if (isset($user['role'])) {
        //         if (is_array($user['role'])) {
        //             $roleName = $user['role']['name'] ?? '';
        //         } else {
        //             $roleName = $user['role'];
        //         }
        //     }
        //     if (strcasecmp($roleName, 'Authoriser') === 0) {
        //         $status = 'published'; // Authorisers publish directly by default
        //     }
        // }

        // Allow explicit status override (e.g. saving as draft)
        if (isset($data['status'])) {
            $status = $data['status'];
        }

        $this->processFeaturedImage($data);

        $article = Article::create([
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'summary' => $data['summary'] ?? null,
            'status' => $status,
            'is_featured' => $data['is_featured'] ?? false,
            'featured_image' => $data['featured_image'] ?? null,
            'inputter_id' => $inputterId,
            'authoriser_id' => $data['authoriser_id'] ?? null,
            'category_id' => $data['category_id'],
        ]);

        if (!empty($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        if ($status === 'pending' && !empty($article->authoriser_id)) {
            resolve(\App\Services\NotificationService::class)->notifyAuthoriserAboutPendingArticle($article, (int)$article->authoriser_id);
        }

        return $this->enrichArticles($article->load(['category', 'tags']));
    }

    /**
     * Check if user is authorized to edit/delete the article.
     */
    public function authorizeUser(Authenticatable $user, Article $article): bool
    {
        return $user->role === 'AUTHORISER' || $article->inputter_id === $user->id;
    }

    /**
     * Update article.
     */
    public function updateArticle(Article $article, array $data): Article
    {
        if (isset($data['title'])) {
            $slug = Str::slug($data['title']);
            $count = Article::where('slug', 'like', $slug . '%')->where('id', '!=', $article->id)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }
            $article->slug = $slug;
        }

        $this->processFeaturedImage($data);

        $article->update($data);

        if (isset($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        return $this->enrichArticles($article->load(['category', 'tags']));
    }

    /**
     * Delete article.
     */
    public function deleteArticle(Article $article): bool
    {
        return $article->delete();
    }

    /**
     * Save article as draft.
     */
    public function saveAsDraft(Article $article): Article
    {
        $article->update(['status' => 'draft']);
        return $this->enrichArticles($article);
    }

    /**
     * Render Markdown to HTML.
     */
    public function renderMarkdown(string $content): string
    {
        $html = nl2br(e($content));
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank">$1</a>', $html);
        return $html;
    }

    /**
     * Publish or submit article for approval.
     */
    public function publishArticle(Article $article, Authenticatable $user): array
    {
        if ($user->role === 'AUTHORISER') {
            $article->update([
                'status' => 'published',
                'authoriser_id' => $user->id
            ]);

            ApprovalHistory::create([
                'article_id' => $article->id,
                'authoriser_id' => $user->id,
                'action' => 'approve',
                'reason' => 'Directly published by Authoriser.'
            ]);

            return [
                'success' => true,
                'message' => 'Article published directly.',
                'article' => $this->enrichArticles($article)
            ];
        } elseif ($user->role === 'INPUTTER') {
            $article->update([
                'status' => 'pending'
            ]);

            // Notify the specific authoriser assigned to the article
            if (!empty($article->authoriser_id)) {
                resolve(\App\Services\NotificationService::class)->notifyAuthoriserAboutPendingArticle($article, (int)$article->authoriser_id);
            }

            return [
                'success' => true,
                'message' => 'Article submitted for approval.',
                'article' => $this->enrichArticles($article)
            ];
        }

        return [
            'success' => false,
            'message' => 'Forbidden.'
        ];
    }

    /**
     * Unpublish article.
     */
    public function unpublishArticle(Article $article): Article
    {
        $article->update(['status' => 'draft']);
        return $this->enrichArticles($article);
    }

    /**
     * Get featured and latest articles.
     */
    public function getFeaturedAndLatest(): array
    {
        $featured = Article::where('status', 'published')
            ->where('is_featured', true)
            ->with(['category', 'tags'])
            ->first();

        $latest = Article::where('status', 'published')
            ->where('is_featured', false)
            ->with(['category', 'tags'])
            ->latest()
            ->limit(5)
            ->get();

        return $this->enrichArticles([
            'featured' => $featured,
            'latest' => $latest
        ]);
    }

    /**
     * Get filterable/paginated public articles.
     */
    public function getPublicArticles(array $filters, string $sort = 'latest', int $limit = 12): LengthAwarePaginator
    {
        $query = Article::where('status', 'published')
            ->with(['category', 'tags']);

        if (!empty($filters['category'])) {
            $category = $filters['category'];
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category)->orWhere('id', $category);
            });
        }

        if (!empty($filters['author'])) {
            $query->where('inputter_id', $filters['author']);
        }

        if (!empty($filters['tag'])) {
            $tag = $filters['tag'];
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('slug', $tag)->orWhere('id', $tag);
            });
        }

        if ($sort === 'latest') {
            $query->latest();
        } elseif ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'views') {
            $query->orderBy('views_count', 'desc');
        } elseif ($sort === 'shares') {
            $query->orderBy('shares_count', 'desc');
        }

        return $this->enrichArticles($query->paginate($limit));
    }

    public function searchArticles(string $searchTerm)
    {
        $inputterIds = [];
        if (!app()->environment('testing')) {
            $userService = resolve(\App\Services\ExternalUserService::class);
            $matchingUsers = $userService->getAllUsers()->filter(function ($user) use ($searchTerm) {
                $name = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
                if (empty($name)) {
                    $name = $user['name'] ?? $user['email'] ?? '';
                }
                return stripos($name, $searchTerm) !== false || stripos($user['email'] ?? '', $searchTerm) !== false;
            });
            $inputterIds = $matchingUsers->pluck('id')->toArray();
        }

        return $this->enrichArticles(Article::where('status', 'published')
            ->where(function ($query) use ($searchTerm, $inputterIds) {
                $query->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('content', 'like', '%' . $searchTerm . '%');
                
                if (!empty($inputterIds)) {
                    $query->orWhereIn('inputter_id', $inputterIds);
                }
                
                $query->orWhereHas('tags', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            })
            ->with(['category', 'tags'])
            ->get());
    }

    public function findBySlug(string $slug): ?Article
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'published')
            ->with(['category', 'tags'])
            ->first();

        return $article ? $this->enrichArticles($article) : null;
    }

    /**
     * Find article by ID.
     */
    public function findArticleById(int $id): ?Article
    {
        $article = Article::with(['category', 'tags'])->find($id);
        return $article ? $this->enrichArticles($article) : null;
    }

    public function getRelatedArticles(Article $article, int $limit = 4)
    {
        $tagIds = $article->tags->pluck('id')->toArray();

        return $this->enrichArticles(Article::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->where(function ($query) use ($article, $tagIds) {
                $query->where('category_id', $article->category_id)
                    ->orWhereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('tags.id', $tagIds);
                    });
            })
            ->with(['category', 'tags'])
            ->limit($limit)
            ->get());
    }

    /**
     * Track a view.
     */
    public function trackView(Article $article): Article
    {
        $article->increment('views_count');
        return $article;
    }

    /**
     * Track a share.
     */
    public function trackShare(Article $article): Article
    {
        $article->increment('shares_count');
        return $article;
    }

    /**
     * Generate simple PDF output for an article.
     */
    public function generatePdf(Article $article): string
    {
        $pdfContent = "%PDF-1.4\n";
        $pdfContent .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdfContent .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdfContent .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> /Contents 4 0 R >>\nendobj\n";
        
        $authorName = 'Unknown';
        if (!empty($article->inputter)) {
            $authorName = is_object($article->inputter) 
                ? ($article->inputter->name ?? 'Unknown') 
                : ($article->inputter['name'] ?? 'Unknown');
        }

        $streamContent = "BT\n/F1 18 Tf\n50 750 Td\n(" . addslashes($article->title) . ") Tj\n/F1 10 Tf\n0 -30 Td\n(Author: " . addslashes($authorName) . ") Tj\n0 -20 Td\n(Summary: " . addslashes($article->summary ?? '') . ") Tj\nET";
        $length = strlen($streamContent);
        
        $pdfContent .= "4 0 obj\n<< /Length $length >>\nstream\n" . $streamContent . "\nendstream\nendobj\n";
        $pdfContent .= "xref\n0 5\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000282 00000 n\n";
        $pdfContent .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n382\n%%EOF";

        return $pdfContent;
    }

    /**
     * Enrich articles with external user data.
     */
    protected function enrichArticles($articles)
    {
        if (app()->environment('testing')) {
            return $articles;
        }

        $userService = resolve(\App\Services\ExternalUserService::class);

        if ($articles instanceof \App\Models\Article) {
            $collection = collect([$articles]);
            $userService->enrichWithUsers($collection, [
                'inputter_id' => 'inputter',
                'authoriser_id' => 'authoriser'
            ]);
            return $articles;
        }

        if ($articles instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $userService->enrichWithUsers($articles, [
                'inputter_id' => 'inputter',
                'authoriser_id' => 'authoriser'
            ]);
        } elseif ($articles instanceof \Illuminate\Support\Collection) {
            $userService->enrichWithUsers($articles, [
                'inputter_id' => 'inputter',
                'authoriser_id' => 'authoriser'
            ]);
        } elseif (is_array($articles)) {
            foreach ($articles as $key => $value) {
                if ($value instanceof \App\Models\Article) {
                    $collection = collect([$value]);
                    $userService->enrichWithUsers($collection, [
                        'inputter_id' => 'inputter',
                        'authoriser_id' => 'authoriser'
                    ]);
                } elseif ($value instanceof \Illuminate\Support\Collection) {
                    $userService->enrichWithUsers($value, [
                        'inputter_id' => 'inputter',
                        'authoriser_id' => 'authoriser'
                    ]);
                }
            }
        }

        return $articles;
    }

    /**
     * Process featured image if uploaded or as base64 string.
     */
    protected function processFeaturedImage(array &$data): void
    {
        if (!array_key_exists('featured_image', $data)) {
            return;
        }

        $imageValue = $data['featured_image'];

        if ($imageValue instanceof \Illuminate\Http\UploadedFile) {
            $filename = time() . '_' . str_replace(' ', '_', $imageValue->getClientOriginalName());
            
            $destinationPath = public_path('featured_image');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $imageValue->move($destinationPath, $filename);
            $data['featured_image'] = url('public/featured_image/' . $filename);
        } elseif (is_string($imageValue) && preg_match('/^data:image\/(\w+);base64,(.*)$/s', $imageValue, $matches)) {
            $extension = $matches[1];
            $base64Data = base64_decode($matches[2]);
            $filename = time() . '_' . uniqid() . '.' . $extension;
            
            $destinationPath = public_path('featured_image');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            file_put_contents($destinationPath . '/' . $filename, $base64Data);
            $data['featured_image'] = url('featured_image/' . $filename);
        }
    }
}
