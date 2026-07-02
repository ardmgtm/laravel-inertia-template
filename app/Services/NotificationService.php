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

    public function getUnreadNotificationCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('read_at', null)
            ->count();
    }

    public function markAsRead(int $notificationId): void
    {
        Notification::where('id', $notificationId)
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->where('read_at', null)
            ->update(['read_at' => now()]);
    }
}
