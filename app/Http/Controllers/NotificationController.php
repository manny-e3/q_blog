<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications globally.
     */
    public function index()
    {
        $notifications = $this->notificationService->getAllNotifications();

        return response()->json($notifications);
    }

    /**
     * Get all notifications for a specific user ID.
     */
    public function getByUserId($userId)
    {
        $notifications = $this->notificationService->getNotificationsByUserId((int) $userId);

        return response()->json($notifications);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $this->notificationService->findUserNotification($user, $id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $updatedNotification = $this->notificationService->markAsRead($notification);

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $updatedNotification
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll()
    {
        $user = Auth::user();
        $this->notificationService->markAllAsRead($user);

        return response()->json([
            'message' => 'All notifications marked as read.'
        ]);
    }

    /**
     * Store/Send a manual notification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $userService = resolve(\App\Services\ExternalUserService::class);
        $user = $userService->getUserById((int)$validated['user_id']);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $email = $user['email'] ?? null;
        if (!$email) {
            return response()->json(['message' => 'User email not found.'], 400);
        }

        $notification = $this->notificationService->sendNotification(
            (int)$validated['user_id'],
            $email,
            $validated['title'],
            $validated['message']
        );

        return response()->json([
            'message' => 'Notification created successfully.',
            'notification' => $notification
        ], 201);
    }
}
