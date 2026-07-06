<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Contracts\Auth\Authenticatable;

class NotificationService
{
    /**
     * Get all notifications for a user.
     */
    public function getNotificationsForUser(Authenticatable $user)
    {
        return Notification::where('user_id', $user->id)
            ->latest()
            ->get();
    }

    /**
     * Find user notification by ID.
     */
    public function findUserNotification(Authenticatable $user, int $id): ?Notification
    {
        return Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->update([
            'read_at' => now()
        ]);

        return $notification;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(Authenticatable $user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now()
            ]);
    }

    /**
     * Send email and create database notification.
     */
    public function sendNotification(int $userId, string $email, string $title, string $message): void
    {
        // 1. Create database notification
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
        ]);

        // 2. Send email
        try {
            \Illuminate\Support\Facades\Mail::send('emails.notification', [
                'title' => $title,
                'bodyMessage' => $message,
            ], function ($mail) use ($email, $title) {
                $mail->to($email)
                     ->subject($title);
            });
            \Log::info('Notification email sent successfully', [
                'email' => $email,
                'title' => $title
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send notification email', [
                'email' => $email,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify all Authorisers when a new article is pending approval.
     */
    public function notifyAuthorisersAboutPendingArticle(\App\Models\Article $article): void
    {
        $userService = resolve(\App\Services\ExternalUserService::class);
        $users = $userService->getAllUsers();
        
        $authorisers = $users->filter(function ($u) {
            $roleName = '';
            if (isset($u['role'])) {
                if (is_array($u['role'])) {
                    $roleName = $u['role']['name'] ?? '';
                } else {
                    $roleName = $u['role'];
                }
            }
            return strcasecmp($roleName, 'Authoriser') === 0;
        });

        $inputter = $userService->getUserById($article->inputter_id);
        $inputterName = $inputter ? trim(($inputter['firstname'] ?? '') . ' ' . ($inputter['lastname'] ?? '')) : 'An author';
        if (empty($inputterName) && $inputter) {
            $inputterName = $inputter['name'] ?? 'An author';
        }

        $title = 'New Article Awaiting Approval';
        $message = "{$inputterName} has submitted a new article: '{$article->title}' and it is awaiting your approval.";

        foreach ($authorisers as $authoriser) {
            if (!empty($authoriser['email'])) {
                $this->sendNotification($authoriser['id'], $authoriser['email'], $title, $message);
            }
        }
    }

    /**
     * Notify a specific Authoriser when a new article is pending approval.
     */
    public function notifyAuthoriserAboutPendingArticle(\App\Models\Article $article, int $authoriserId): void
    {
        $userService = resolve(\App\Services\ExternalUserService::class);
        $authoriser = $userService->getUserById($authoriserId);

        if (!$authoriser) {
            \Log::warning('Failed to notify authoriser: User not found', ['authoriser_id' => $authoriserId]);
            return;
        }

        $inputter = $userService->getUserById($article->inputter_id);
        $inputterName = $inputter ? trim(($inputter['firstname'] ?? '') . ' ' . ($inputter['lastname'] ?? '')) : 'An author';
        if (empty($inputterName) && $inputter) {
            $inputterName = $inputter['name'] ?? 'An author';
        }

        $title = 'New Article Awaiting Approval';
        $message = "{$inputterName} has submitted a new article: '{$article->title}' and it is awaiting your approval.";

        if (!empty($authoriser['email'])) {
            $this->sendNotification($authoriser['id'], $authoriser['email'], $title, $message);
        }
    }

    /**
     * Notify Inputter about approval or rejection.
     */
    public function notifyInputterAboutResolution(\App\Models\Article $article, string $status, ?string $reason = null): void
    {
        $userService = resolve(\App\Services\ExternalUserService::class);
        $inputter = $userService->getUserById($article->inputter_id);

        if ($inputter && !empty($inputter['email'])) {
            $statusText = $status === 'published' ? 'approved and published' : 'rejected';
            $title = "Article Submission " . ucfirst($statusText);
            
            $message = "Your article titled '{$article->title}' has been {$statusText}.";
            if ($status === 'rejected' && $reason) {
                $message .= "\n\nReason for rejection: {$reason}";
            }

            $this->sendNotification($inputter['id'], $inputter['email'], $title, $message);
        }
    }
}
