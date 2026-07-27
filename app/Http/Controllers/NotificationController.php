<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function getNotificationList(Request $request)
    {
        try {
            $notifications = $this->notificationService->getNotificationList($this->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Success to get notifications',
                'data' => $notifications,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications',
            ], 500);
        }
    }

    public function getUnreadNotificationList(Request $request)
    {
        try {
            $notifications = $this->notificationService->getUnreadNotificationList($this->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Success to get notifications',
                'data' => $notifications,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications',
            ], 500);
        }
    }

    public function markAsRead(Request $request, string $id)
    {
        try {
            $notification = $this->notificationService->markAsRead($id);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $notification,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
            ], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $this->notificationService->markAllAsRead($this->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
            ], 500);
        }
    }
}
