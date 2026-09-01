<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\NewsletterSubscription;
use App\Mail\ArticlePublishedAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifySubscribersOfPublishedArticle implements ShouldQueue
{
    use Queueable;

    protected $article;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $articleCategories = $this->article->categories->pluck('name')->toArray();

        $subscribers = NewsletterSubscription::where('status', 'active')->get();

        foreach ($subscribers as $subscriber) {
            $userTopics = $subscriber->topics ?? [];

            if (!empty($userTopics)) {
                $intersect = array_intersect($articleCategories, $userTopics);
                if (empty($intersect)) {
                    continue;
                }
            }

            try {
                Mail::to($subscriber->email)->send(new ArticlePublishedAlert($this->article, $subscriber));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send article published alert', [
                    'email' => $subscriber->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
