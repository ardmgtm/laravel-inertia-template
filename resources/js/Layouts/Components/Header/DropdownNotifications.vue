<template>
  <OverlayBadge :value="unreadCount > 0 ? unreadCount : undefined" severity="danger">
    <Button icon="pi pi-bell" severity="secondary" variant="text" rounded @click="openNotification" />
  </OverlayBadge>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const emit = defineEmits<{
  'open-drawer': [];
}>();

const unreadCount = ref(0);

onMounted(() => {
  loadUnreadCount();
});

async function loadUnreadCount() {
  try {
    const response = await window.axios.get('/api/notification/unread');
    if (response.data.success) {
      unreadCount.value = response.data.data.length;
    }
  } catch (error) {
    console.error('Failed to load unread notification count:', error);
  }
}

function openNotification() {
  emit('open-drawer');
}

// Expose method to refresh count from parent
defineExpose({
  loadUnreadCount
});
</script>