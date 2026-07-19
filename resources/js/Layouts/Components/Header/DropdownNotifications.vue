<template>
  <OverlayBadge :value="notificationStore.unreadCount > 0 ? notificationStore.unreadCount : undefined" severity="danger">
    <Button icon="pi pi-bell" severity="secondary" variant="text" rounded @click="openNotification" />
  </OverlayBadge>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useNotificationStore } from '@/Stores/notification-store';

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