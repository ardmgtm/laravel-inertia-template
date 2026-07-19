<template>
  <div
    class="p-4 border rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
    :class="{ 'bg-blue-50 border-blue-200': !notification.read_at }"
    @click="$emit('click')"
  >
    <div class="flex items-start gap-3">
      <div class="flex-1 min-w-0">
        <div class="flex justify-between items-center gap-2 mb-2">
          <div class="flex-shrink-0">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center"
              :class="notification.read_at ? 'bg-gray-200 text-gray-600' : 'bg-blue-100 text-blue-600'"
            >
              <i :class="'pi pi-bell'" class="text-lg"></i>
            </div>
          </div>
          <div class="flex items-start justify-between gap-2 mb-1 flex-1 min-w-0">
            <h3 class="font-semibold text-sm text-gray-900" :class="{ 'font-bold': !notification.read_at }">
              {{ notification.data.title }}
            </h3>
            <span v-if="!notification.read_at" class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-1.5"></span>
          </div>
        </div>
        <p class="text-sm text-gray-600 mb-2">{{ notification.data.message }}</p>
        <div class="flex items-center justify-between">
          <span class="text-xs text-gray-400">{{ formatDate(notification.created_at) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Notification from '@/Core/Models/notification';
import { useNotificationStore } from '@/Stores/notification-store';

defineProps<{
  notification: Notification;
}>();

defineEmits<{
  click: [];
  'mark-read': [];
}>();

const notificationStore = useNotificationStore();

function formatDate(dateString: string | null | undefined): string {
  return dateString ? notificationStore.formatDate(dateString) : '';
}
</script>
