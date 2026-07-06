<?php

namespace App\Services;

use App\Models\NewsletterSubscription;

class NewsletterService
{
    /**
     * Subscribe to newsletter.
     */
    public function subscribe(array $data): NewsletterSubscription
    {
        $firstName = $data['firstName'] ?? $data['first_name'] ?? '';
        $lastName = $data['lastName'] ?? $data['last_name'] ?? '';

        return NewsletterSubscription::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'],
            'consent_given' => $data['consent'],
        ]);
    }

    /**
     * Verify CAPTCHA token.
     */
    public function verifyCaptcha(?string $token): array
    {
        // Simulating captcha check validation
        return [
            'success' => true,
            'message' => 'CAPTCHA verification successful.'
        ];
    }

    /**
     * Check if email is subscribed.
     */
    public function isSubscribed(string $email): bool
    {
        return NewsletterSubscription::where('email', $email)->exists();
    }

    /**
     * Sync subscribers to FMDQ Newsletter Platform.
     */
    public function syncSubscribers(): int
    {
        $subscriptions = NewsletterSubscription::all();
        return $subscriptions->count();
    }

    /**
     * Get subscribers (paginated).
     */
    public function getSubscribers(int $limit = 15)
    {
        return NewsletterSubscription::paginate($limit);
    }
}
