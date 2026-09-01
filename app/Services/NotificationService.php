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
     * Get all notifications globally in the system.
     */
    public function getAllNotifications()
    {
        return Notification::latest()->get();
    }

    /**
     * Get all notifications for a user by their user ID.
     */
    public function getNotificationsByUserId(int $userId)
    {
        return Notification::where('user_id', $userId)
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
    public function sendNotification(int $userId, string $email, string $title, string $message): Notification
    {
        // 1. Create database notification
        $notification = Notification::create([
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

        return $notification;
    }

    /**
     * Notify all Authorisers when a new article is pending approval.
     */
    public function notifyAuthorisersAboutPendingArticle(\App\Models\Article $article, bool $isUpdate = false): void
    {
        $userService = resolve(\App\Services\ExternalUserService::class);
        $users = $userService->getAllUsers();
        
        $authorisers = $users->filter(function ($u) {
            if (isset($u['role_id']) && $u['role_id'] == 67) {
                return true;
            }
            if (isset($u['role'])) {
                if (is_array($u['role'])) {
                    if (isset($u['role']['id']) && $u['role']['id'] == 67) {
                        return true;
                    }
                } else {
                    if ($u['role'] == 67) {
                        return true;
                    }
                }
            }
            if (isset($u['id']) && $u['id'] == 999) {
                return true;
            }
            return false;
        });

        $inputter = $userService->getUserById($article->inputter_id);
        $inputterName = $inputter ? trim(($inputter['firstname'] ?? '') . ' ' . ($inputter['lastname'] ?? '')) : 'An author';
        if (empty($inputterName) && $inputter) {
            $inputterName = $inputter['name'] ?? 'An author';
        }

        $title = $isUpdate ? 'Article Update Awaiting Approval' : 'New Article Awaiting Approval';
        $actionText = $isUpdate ? 'updated the article' : 'submitted a new article';
        $message = "{$inputterName} has {$actionText}: '{$article->title}' and it is awaiting your approval.";

        foreach ($authorisers as $authoriser) {
            if (!empty($authoriser['email'])) {
                $this->sendNotification($authoriser['id'], $authoriser['email'], $title, $message);
            }
        }
    }

    /**
     * Notify a specific Authoriser when a new article is pending approval.
     */
    public function notifyAuthoriserAboutPendingArticle(\App\Models\Article $article, int $authoriserId, bool $isUpdate = false): void
    {
        $userService = resolve(\App\Services\ExternalUserService::class);
        if ($authoriserId === 999) {
            $authoriser = [
                'id' => 999,
                'firstname' => 'System',
                'lastname' => 'Integrator',
                'email' => 'integrator@test.com',
                'role' => 'AUTHORISER',
                'status' => 'active'
            ];
        } else {
            $authoriser = $userService->getUserById($authoriserId);
        }

        if (!$authoriser) {
            \Log::warning('Failed to notify authoriser: User not found', ['authoriser_id' => $authoriserId]);
            return;
        }

        $inputter = $userService->getUserById($article->inputter_id);
        $inputterName = $inputter ? trim(($inputter['firstname'] ?? '') . ' ' . ($inputter['lastname'] ?? '')) : 'An author';
        if (empty($inputterName) && $inputter) {
            $inputterName = $inputter['name'] ?? 'An author';
        }

        $title = $isUpdate ? 'Article Update Awaiting Approval' : 'New Article Awaiting Approval';
        $actionText = $isUpdate ? 'updated the article' : 'submitted a new article';
        $message = "{$inputterName} has {$actionText}: '{$article->title}' and it is awaiting your approval.";

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
        if ($article->inputter_id === 999) {
            $inputter = [
                'id' => 999,
                'firstname' => 'System',
                'lastname' => 'Integrator',
                'email' => 'integrator@test.com',
                'role' => 'AUTHORISER',
                'status' => 'active'
            ];
        } else {
            $inputter = $userService->getUserById($article->inputter_id);
        }

        if ($inputter && !empty($inputter['email'])) {
            $statusText = $status === 'published' ? 'approved and published' : 'rejected';
            $title = "Article Submission " . ucfirst($statusText);
            
            $message = "Your article titled '{$article->title}' has been {$statusText}.";
            if ($status === 'rejected' && $reason) {
                $message .= "\n\nReason for rejection: {$reason}";
            }

            // 1. Create database notification
            Notification::create([
                'user_id' => $inputter['id'],
                'title' => $title,
                'message' => $message,
            ]);

            // 2. Fetch authoriser info if available
            $authoriserName = 'System Authoriser';
            if ($article->authoriser_id) {
                $authoriser = $userService->getUserById($article->authoriser_id);
                if ($authoriser) {
                    $authoriserName = trim(($authoriser['firstname'] ?? '') . ' ' . ($authoriser['lastname'] ?? ''));
                    if (empty($authoriserName)) {
                        $authoriserName = $authoriser['name'] ?? 'System Authoriser';
                    }
                }
            }

            // 3. Send email using the custom template
            try {
                $actionUrl = $status === 'published' ? url('/articles/' . $article->slug) : url('/tester');
                $inputterName = trim(($inputter['firstname'] ?? '') . ' ' . ($inputter['lastname'] ?? ''));
                if (empty($inputterName)) {
                    $inputterName = $inputter['name'] ?? 'Author';
                }

                \Illuminate\Support\Facades\Mail::to($inputter['email'])->send(new \App\Mail\ArticleResolutionMail(
                    $title,
                    $article,
                    $status,
                    $statusText,
                    $authoriserName,
                    $reason,
                    $actionUrl,
                    $inputterName
                ));
                
                \Log::info('Article resolution email sent successfully', [
                    'email' => $inputter['email'],
                    'title' => $title
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send article resolution email', [
                    'email' => $inputter['email'],
                    'title' => $title,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Notify Inputter that their article has been successfully submitted and is pending approval.
     */
    public function notifyInputterAboutSubmission(\App\Models\Article $article): void
    {
        $userService = resolve(\App\Services\ExternalUserService::class);
        if ($article->inputter_id === 999) {
            $inputter = [
                'id' => 999,
                'firstname' => 'System',
                'lastname' => 'Integrator',
                'email' => 'integrator@test.com',
                'role' => 'AUTHORISER',
                'status' => 'active'
            ];
        } else {
            $inputter = $userService->getUserById($article->inputter_id);
        }

        if ($inputter && !empty($inputter['email'])) {
            $title = 'Article Submitted Awaiting Approval';
            $message = "Your article: '{$article->title}' has been successfully submitted and is awaiting approval.";
            $this->sendNotification($inputter['id'], $inputter['email'], $title, $message);
        }
    }

    /**
     * Notify Inputter that their article has been unpublished (returned to draft).
     */
    public function notifyInputterAboutUnpublish(\App\Models\Article $article): void
    {
        $userService = resolve(\App\Services\ExternalUserService::class);
        if ($article->inputter_id === 999) {
            $inputter = [
                'id' => 999,
                'firstname' => 'System',
                'lastname' => 'Integrator',
                'email' => 'integrator@test.com',
                'role' => 'AUTHORISER',
                'status' => 'active'
            ];
        } else {
            $inputter = $userService->getUserById($article->inputter_id);
        }

        if ($inputter && !empty($inputter['email'])) {
            $title = 'Article Unpublished';
            $message = "Your article: '{$article->title}' has been unpublished and returned to draft status.";
            $this->sendNotification($inputter['id'], $inputter['email'], $title, $message);
        }
    }
}
