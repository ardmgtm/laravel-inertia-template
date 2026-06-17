<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function getNotificationList(int $userId): array
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getUnreadNotificationList(int $userId): array
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->where('read_at', null)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getUnreadNotificationCount(int $userId): array
    {
        $count = Notification::where('user_id', $userId)
                    ->where('read_at', null)
                    ->count();
        return [
            'success' => true, 
            'message' => 'Unread notification count retrieved successfully', 
            'data' => $count
        ];
    }

    public function markAsRead(int $notificationId): array
    {
        Notification::where('id', $notificationId)
            ->update(['read_at' => now()]);
        return [
            'success' => true, 
            'message' => 'Notification marked as read successfully'
        ];
    }

    public function markAllAsRead(int $userId): array
    {
        Notification::where('user_id', $userId)
            ->where('read_at', null)
            ->update(['read_at' => now()]);
        return [
            'success' => true, 
            'message' => 'All notifications marked as read successfully'
        ];
    }
}
