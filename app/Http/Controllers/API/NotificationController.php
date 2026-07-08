<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
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

            return JsonResponse::success(
                'Success to get notifications',
                $notifications
            );
        } catch (\Throwable $th) {
            return JsonResponse::failed('Failed to get notifications');
        }
    }

    public function getUnreadNotificationList(Request $request)
    {
        try {
            $notifications = $this->notificationService->getUnreadNotificationList($this->user()->id);

            return JsonResponse::success(
                'Success to get notifications',
                $notifications
            );
        } catch (\Throwable $th) {
            return JsonResponse::failed('Failed to get notifications');
        }
    }

    public function markAsRead(Request $request, string $id)
    {
        try {
            $this->notificationService->markAsRead($id);

            return JsonResponse::success('Notification marked as read');
        } catch (\Throwable $th) {
            return JsonResponse::failed('Failed to mark notification as read');
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $this->notificationService->markAllAsRead($this->user()->id);

            return JsonResponse::success('All notifications marked as read');
        } catch (\Throwable $th) {
            return JsonResponse::failed('Failed to mark all notifications as read');
        }
    }
}
