<template>
  <Component :is="notificationStore.unreadCount > 0 ? OverlayBadge : 'div'" :value="notificationStore.unreadCount > 0 ? notificationStore.unreadCount : undefined"
    severity="danger">
    <Button severity="secondary" variant="text" @click="openNotification">
      <i class="pi pi-bell"></i>
    </Button>
  </Component>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useNotificationStore } from '@/Stores/notification-store';
import { OverlayBadge } from 'primevue';

const emit = defineEmits<{
  'open-drawer': [];
}>();

const notificationStore = useNotificationStore();

onMounted(() => {
  // Load notifications on mount to get unread count
  loadUnreadCount();
});

async function loadUnreadCount() {
  try {
    await notificationStore.loadNotifications();
  } catch (error) {
    console.error('Failed to load unread count:', error);
  }
}

function openNotification() {
  emit('open-drawer');
}

// Expose method for external refresh
defineExpose({
  loadUnreadCount
});
</script>