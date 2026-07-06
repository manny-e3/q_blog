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
     * Get all notifications for authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getNotificationsForUser($user);

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
}
