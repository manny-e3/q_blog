<?php

namespace App\Http\Controllers;

use App\Services\NewsletterService;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    protected $newsletterService;

    public function __construct(NewsletterService $newsletterService)
    {
        $this->newsletterService = $newsletterService;
    }

    /**
     * Subscribe to newsletter.
     */
    public function subscribe(Request $request)
    {
        // Support both snake_case and camelCase parameters
        $validated = $request->validate([
            'firstName' => 'sometimes|string|max:100',
            'first_name' => 'sometimes|string|max:100',
            'lastName' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'required|email|unique:newsletter_subscriptions,email',
            'consent' => 'required|boolean',
            'captchaToken' => 'nullable|string',
            'captcha_token' => 'nullable|string',
        ]);

        $subscription = $this->newsletterService->subscribe($validated);

        return response()->json([
            'message' => 'Successfully subscribed to the Q-BLOG newsletter.',
            'subscription' => $subscription
        ], 201);
    }

    /**
     * Verify CAPTCHA token.
     */
    public function verifyCaptcha(Request $request)
    {
        $validated = $request->validate([
            'captchaToken' => 'sometimes|string',
            'captcha_token' => 'sometimes|string',
        ]);

        $token = $validated['captchaToken'] ?? $validated['captcha_token'] ?? null;
        $result = $this->newsletterService->verifyCaptcha($token);

        return response()->json($result);
    }

    /**
     * Check if email is already subscribed.
     */
    public function check(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $exists = $this->newsletterService->isSubscribed($request->email);

        return response()->json([
            'email' => $request->email,
            'is_subscribed' => $exists
        ]);
    }

    /**
     * Sync to FMDQ Newsletter Platform.
     */
    public function sync()
    {
        $syncCount = $this->newsletterService->syncSubscribers();

        return response()->json([
            'success' => true,
            'message' => "Successfully synced $syncCount subscribers to the FMDQ Newsletter Platform.",
            'synced_count' => $syncCount
        ]);
    }

    /**
     * Get all subscribers (paginated).
     */
    public function subscribers(Request $request)
    {
        $limit = $request->input('limit', 15);
        $subscribers = $this->newsletterService->getSubscribers((int)$limit);

        return response()->json($subscribers);
    }
}
