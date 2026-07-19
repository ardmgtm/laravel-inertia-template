import Notification from '@/Core/Models/notification';
import { defineStore } from 'pinia';
import axios from 'axios';

interface NotificationState {
    notifications: Notification[];
    loading: boolean;
    loadingMore: boolean;
    markingAllAsRead: boolean;
    hasMore: boolean;
    page: number;
}

export const useNotificationStore = defineStore('notification', {
    state: (): NotificationState => ({
        notifications: [],
        loading: false,
        loadingMore: false,
        markingAllAsRead: false,
        hasMore: false,
        page: 1,
    }),

    getters: {
        unreadCount: (state): number => {
            return state.notifications.filter(n => !n.read_at).length;
        },

        unreadNotifications: (state): Notification[] => {
            return state.notifications.filter(n => !n.read_at);
        },

        readNotifications: (state): Notification[] => {
            return state.notifications.filter(n => n.read_at);
        },

        allNotifications: (state): Notification[] => {
            return state.notifications;
        },
    },

    actions: {
        async loadNotifications(force = false) {
            // Avoid loading if already loaded unless forced
            if (this.notifications.length > 0 && !force) {
                return;
            }

            this.loading = true;
            try {
                const response = await axios.get('/api/notification');
                if (response.data.status) {
                    this.notifications = response.data.data;
                    this.hasMore = false; // Update when pagination is implemented
                    this.page = 1;
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async loadMore() {
            if (!this.hasMore || this.loadingMore) {
                return;
            }

            this.loadingMore = true;
            this.page++;

            try {
                // TODO: Implement pagination when backend supports it
                const response = await axios.get('/api/notification', {
                    params: { page: this.page }
                });
                
                if (response.data.status) {
                    this.notifications.push(...response.data.data);
                    // Update hasMore based on pagination metadata
                    this.hasMore = response.data.hasMore || false;
                }
            } catch (error) {
                console.error('Failed to load more notifications:', error);
                this.page--; // Revert page increment on error
                throw error;
            } finally {
                this.loadingMore = false;
            }
        },

        async markAsRead(notificationId: string) {
            try {
                const response = await axios.put(`/api/notification/${notificationId}/read`);
                
                if (response.data.status) {
                    // Update notification with data from response
                    const index = this.notifications.findIndex(n => n.id === notificationId);
                    if (index !== -1 && response.data.data) {
                        this.notifications[index] = response.data.data;
                    }
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
                throw error;
            }
        },

        async markAllAsRead() {
            this.markingAllAsRead = true;
            try {
                const response = await axios.put('/api/notification/read-all');
                
                if (response.data.status) {
                    // Mark all notifications as read with current timestamp
                    const currentTime = new Date().toISOString();
                    this.notifications.forEach(notification => {
                        if (!notification.read_at) {
                            notification.read_at = currentTime;
                        }
                    });
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Failed to mark all notifications as read:', error);
                throw error;
            } finally {
                this.markingAllAsRead = false;
            }
        },

        async refreshNotifications() {
            await this.loadNotifications(true);
        },

        // Get unread notifications (action wrapper for getter)
        getUnreadNotifications(): Notification[] {
            return this.unreadNotifications;
        },

        // Get all notifications (action wrapper for getter)
        getAllNotifications(): Notification[] {
            return this.allNotifications;
        },

        // Add a new notification (useful for real-time updates via Echo)
        addNotification(notification: Notification) {
            // Add to the beginning of the array
            this.notifications.unshift(notification);
        },

        // Remove a notification
        removeNotification(notificationId: string) {
            const index = this.notifications.findIndex(n => n.id === notificationId);
            if (index !== -1) {
                this.notifications.splice(index, 1);
            }
        },

        // Clear all notifications
        clearNotifications() {
            this.notifications = [];
            this.page = 1;
            this.hasMore = false;
        },

        // Format date utility
        formatDate(dateString: string): string {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

            if (diffInSeconds < 60) {
                return 'Just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
            } else if (diffInSeconds < 604800) {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} ${days === 1 ? 'day' : 'days'} ago`;
            } else {
                return date.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            }
        },
    },
});
