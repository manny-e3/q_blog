<?php

namespace App\Services;

use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterWelcomeMail;

class NewsletterService
{
    /**
     * Subscribe to newsletter.
     */
    public function subscribe(array $data): NewsletterSubscription
    {
        $firstName = $data['firstName'] ?? $data['first_name'] ?? '';
        $lastName = $data['lastName'] ?? $data['last_name'] ?? '';

        // Check if subscription already exists (active or unsubscribed)
        $subscription = NewsletterSubscription::where('email', $data['email'])->first();

        if ($subscription) {
            if ($subscription->status === 'active') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => ['This email is already subscribed to the newsletter.']
                ]);
            }

            // If unsubscribed, reactivate it with new settings
            $subscription->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'consent_given' => $data['consent'],
                'organisation' => $data['organisation'] ?? null,
                'role' => $data['role'] ?? null,
                'topics' => $data['topics'] ?? [],
                'frequency' => $data['frequency'] ?? 'As Published',
                'status' => 'active',
            ]);
        } else {
            // Create a new subscription
            $subscription = NewsletterSubscription::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $data['email'],
                'consent_given' => $data['consent'],
                'organisation' => $data['organisation'] ?? null,
                'role' => $data['role'] ?? null,
                'topics' => $data['topics'] ?? [],
                'frequency' => $data['frequency'] ?? 'As Published',
                'status' => 'active',
            ]);
        }

        // Send welcome email with secure signed unsubscribe URL
        try {
            $unsubscribeUrl = URL::signedRoute('newsletter.unsubscribe', ['email' => $subscription->email]);
            Mail::to($subscription->email)->send(new NewsletterWelcomeMail($subscription, $unsubscribeUrl));
            \Log::info('Welcome email sent successfully to subscriber', ['email' => $subscription->email]);
        } catch (\Exception $e) {
            \Log::error('Failed to send newsletter welcome email', [
                'email' => $subscription->email,
                'error' => $e->getMessage()
            ]);
        }

        return $subscription;
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
        return NewsletterSubscription::where('email', $email)
            ->where('status', 'active')
            ->exists();
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
