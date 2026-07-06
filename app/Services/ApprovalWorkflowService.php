<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ApprovalHistory;
use Illuminate\Contracts\Auth\Authenticatable;

class ApprovalWorkflowService
{
    /**
     * Approve a pending article.
     */
    public function approve(Article $article, Authenticatable $user): Article
    {
        $article->update([
            'status' => 'published',
            'authoriser_id' => $user->id,
            'reject_reason' => null
        ]);

        ApprovalHistory::create([
            'article_id' => $article->id,
            'authoriser_id' => $user->id,
            'action' => 'approve',
            'reason' => 'Approved and published.'
        ]);

        if (!app()->environment('testing')) {
            resolve(\App\Services\ExternalUserService::class)->enrichWithUsers(collect([$article]), [
                'inputter_id' => 'inputter',
                'authoriser_id' => 'authoriser'
            ]);
        }

        // Notify inputter about approval
        resolve(\App\Services\NotificationService::class)->notifyInputterAboutResolution($article, 'published');

        return $article;
    }

    /**
     * Reject a pending article.
     */
    public function reject(Article $article, Authenticatable $user, string $reason): Article
    {
        $article->update([
            'status' => 'rejected',
            'reject_reason' => $reason
        ]);

        ApprovalHistory::create([
            'article_id' => $article->id,
            'authoriser_id' => $user->id,
            'action' => 'reject',
            'reason' => $reason
        ]);

        if (!app()->environment('testing')) {
            resolve(\App\Services\ExternalUserService::class)->enrichWithUsers(collect([$article]), [
                'inputter_id' => 'inputter',
                'authoriser_id' => 'authoriser'
            ]);
        }

        // Notify inputter about rejection
        resolve(\App\Services\NotificationService::class)->notifyInputterAboutResolution($article, 'rejected', $reason);

        return $article;
    }

    /**
     * Get approval history for an article.
     */
    public function getHistory(Article $article)
    {
        $history = ApprovalHistory::where('article_id', $article->id)
            ->latest()
            ->get();

        if (!app()->environment('testing')) {
            resolve(\App\Services\ExternalUserService::class)->enrichWithUsers($history, [
                'authoriser_id' => 'authoriser'
            ]);
        }

        return $history;
    }

    /**
     * Get pending approvals count.
     */
    public function getPendingCount(): int
    {
        return Article::where('status', 'pending')->count();
    }
}
