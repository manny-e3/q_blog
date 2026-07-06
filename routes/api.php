<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\CmsArticleController;
use App\Http\Controllers\MyArticleController;
use App\Http\Controllers\AdminArticleController;
use App\Http\Controllers\ApprovalWorkflowController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\SystemUtilityController;

Route::prefix('v1')->group(function () {

    // 1. Authentication Module
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware('basic.auth');



    // 4. Public Articles Module
    Route::get('/articles/featured', [PublicArticleController::class, 'featured']);
    Route::get('/articles', [PublicArticleController::class, 'index']);
    Route::get('/articles/search', [PublicArticleController::class, 'search']);
    Route::get('/articles/{slug}', [PublicArticleController::class, 'showBySlug']);
    Route::get('/articles/{id}/related', [PublicArticleController::class, 'related']);
    Route::get('/articles/{id}/pdf', [PublicArticleController::class, 'downloadPdf']);
    Route::post('/articles/{id}/view', [PublicArticleController::class, 'trackView']);
    Route::post('/articles/{id}/share', [PublicArticleController::class, 'trackShare']);

    // 5. CMS Article Management
    Route::middleware(['basic.auth'])->group(function () {
        Route::get('/cms/articles/{id}', [CmsArticleController::class, 'show']);
        Route::post('/cms/articles', [CmsArticleController::class, 'store']);
        Route::patch('/cms/articles/{id}', [CmsArticleController::class, 'update']);
        Route::delete('/cms/articles/{id}', [CmsArticleController::class, 'destroy']);
        Route::post('/cms/articles/{id}/save-draft', [CmsArticleController::class, 'saveDraft']);
        Route::post('/cms/articles/preview', [CmsArticleController::class, 'preview']);
        Route::post('/cms/articles/{id}/publish', [CmsArticleController::class, 'publish']);
        Route::post('/cms/articles/{id}/unpublish', [CmsArticleController::class, 'unpublish']);
    });

    // 6. My Articles Module
    Route::middleware(['basic.auth'])->group(function () {
        Route::get('/cms/my-articles/published', [MyArticleController::class, 'published']);
        Route::get('/cms/my-articles/drafts', [MyArticleController::class, 'drafts']);
        Route::get('/cms/my-articles/pending', [MyArticleController::class, 'pending']);
        Route::get('/cms/my-articles/rejected', [MyArticleController::class, 'rejected']);
    });

    // 7. Admin Article Management
    Route::middleware(['basic.auth'])->group(function () {
        Route::get('/cms/admin/articles/published', [AdminArticleController::class, 'published']);
        Route::get('/cms/admin/articles/unpublished', [AdminArticleController::class, 'unpublished']);
        Route::get('/cms/admin/articles/pending', [AdminArticleController::class, 'pending']);
        Route::get('/cms/admin/articles/rejected', [AdminArticleController::class, 'rejected']);
    });

    // 8. Approval Workflow Module
    Route::middleware(['basic.auth'])->group(function () {
        Route::post('/approvals/{articleId}/approve', [ApprovalWorkflowController::class, 'approve']);
        Route::post('/approvals/{articleId}/reject', [ApprovalWorkflowController::class, 'reject']);
        Route::get('/approvals/{articleId}/history', [ApprovalWorkflowController::class, 'history']);
        Route::get('/approvals/pending/count', [ApprovalWorkflowController::class, 'pendingCount']);
    });

    // 9. Category Management
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::middleware(['basic.auth'])->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::patch('/categories/{id}', [CategoryController::class, 'update']);
        Route::patch('/categories/{id}/deactivate', [CategoryController::class, 'deactivate']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    // 10. Tag Management
    Route::get('/tags', [TagController::class, 'index']);
    Route::middleware(['basic.auth'])->group(function () {
        Route::post('/tags', [TagController::class, 'store']);
        Route::patch('/tags/{id}', [TagController::class, 'update']);
        Route::delete('/tags/{id}', [TagController::class, 'destroy']);
    });

    // 11. Media Management
    Route::middleware(['basic.auth'])->group(function () {
        Route::post('/media/upload', [MediaController::class, 'upload']);
        Route::get('/media', [MediaController::class, 'index']);
        Route::delete('/media/{id}', [MediaController::class, 'destroy']);
        Route::patch('/media/{id}/alt-text', [MediaController::class, 'updateAltText']);
    });

    // 12. Notifications Module
    Route::middleware('basic.auth')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    });

    // 13. Analytics Module
    Route::middleware(['basic.auth'])->group(function () {
        Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/analytics/articles', [AnalyticsController::class, 'articles']);
        Route::get('/analytics/top-articles', [AnalyticsController::class, 'topArticles']);
        Route::get('/analytics/top-categories', [AnalyticsController::class, 'topCategories']);
        Route::get('/analytics/top-authors', [AnalyticsController::class, 'topAuthors']);
        Route::get('/analytics/traffic-sources', [AnalyticsController::class, 'trafficSources']);
        Route::get('/analytics/reading-time', [AnalyticsController::class, 'readingTime']);
        Route::get('/analytics/shares', [AnalyticsController::class, 'shares']);
        Route::get('/analytics/export/csv', [AnalyticsController::class, 'exportCsv']);
        Route::get('/analytics/export/pdf', [AnalyticsController::class, 'exportPdf']);
    });

    // 14. Newsletter Subscription Module
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
    Route::post('/newsletter/verify-captcha', [NewsletterController::class, 'verifyCaptcha']);
    Route::get('/newsletter/check', [NewsletterController::class, 'check']);
    Route::post('/newsletter/sync', [NewsletterController::class, 'sync'])->middleware(['basic.auth']);
    Route::get('/cms/subscribers', [NewsletterController::class, 'subscribers'])->middleware(['basic.auth']);

    // 15. System Utilities
    Route::post('/system/generate-slug', [SystemUtilityController::class, 'generateSlug']);
    Route::post('/system/reading-time', [SystemUtilityController::class, 'readingTime']);
    Route::get('/health', [SystemUtilityController::class, 'health']);
});
