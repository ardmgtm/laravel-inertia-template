<template>
  <Drawer v-model:visible="visible" header="Notifikasi" position="right" class="!w-[22rem]">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h2 class="text-lg font-semibold">Notifikasi</h2>
        <Button v-if="unreadCount > 0" label="Tandai Semua Dibaca" text size="small" @click="markAllAsRead"
          :loading="markingAllAsRead" />
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="p-4 border rounded-lg animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
        <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-12">
      <i class="pi pi-bell text-6xl text-gray-300 mb-4"></i>
      <p class="text-gray-500 text-center">Tidak ada notifikasi</p>
    </div>

    <!-- Notification List -->
    <div v-else class="space-y-2">
      <div v-for="notification in notifications" :key="notification.id"
        class="p-4 border rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
        :class="{ 'bg-blue-50 border-blue-200': !notification.read_at }" @click="handleNotificationClick(notification)">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full flex items-center justify-center"
              :class="notification.read_at ? 'bg-gray-200 text-gray-600' : 'bg-blue-100 text-blue-600'">
              <i :class="notification.data.icon || 'pi pi-bell'" class="text-lg"></i>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2 mb-1">
              <h3 class="font-semibold text-sm text-gray-900" :class="{ 'font-bold': !notification.read_at }">
                {{ notification.data.title }}
              </h3>
              <span v-if="!notification.read_at"
                class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-1.5"></span>
            </div>
            <p class="text-sm text-gray-600 mb-2">{{ notification.data.message }}</p>
            <div class="flex items-center justify-between">
              <span class="text-xs text-gray-400">{{ formatDate(notification.created_at) }}</span>
              <Button v-if="!notification.read_at" label="Tandai Dibaca" text size="small"
                @click.stop="markAsRead(notification.id)" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More -->
    <div v-if="hasMore && !loading" class="mt-4">
      <Button label="Muat Lebih Banyak" outlined severity="secondary" class="w-full" @click="loadMore"
        :loading="loadingMore" />
    </div>
  </Drawer>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Notification from '@/Core/Models/notification';
import { router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

const props = defineProps<{
  modelValue: boolean;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: boolean];
  'notificationRead': [];
}>();

const toast = useToast();

const visible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const notifications = ref<Notification[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const markingAllAsRead = ref(false);
const hasMore = ref(false);
const page = ref(1);

const unreadCount = computed(() => 
  notifications.value.filter(n => !n.read_at).length
);

// Load notifications when drawer opens
watch(visible, (isVisible) => {
  if (isVisible && notifications.value.length === 0) {
    loadNotifications();
  }
});

async function loadNotifications() {
  loading.value = true;
  try {
    const response = await window.axios.get('/api/notification');
    if (response.data.success) {
      notifications.value = response.data.data;
      // For now, we don't have pagination, so hasMore is false
      hasMore.value = false;
    }
  } catch (error) {
    console.error('Failed to load notifications:', error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Gagal memuat notifikasi',
      life: 3000
    });
  } finally {
    loading.value = false;
  }
}

async function loadMore() {
  loadingMore.value = true;
  page.value++;
  // TODO: Implement pagination if needed
  loadingMore.value = false;
}

async function markAsRead(notificationId: string) {
  try {
    const response = await window.axios.put(`/api/notification/${notificationId}/read`);
    if (response.data.success) {
      const notification = notifications.value.find(n => n.id === notificationId);
      if (notification) {
        notification.read_at = new Date().toISOString();
      }
      emit('notificationRead');
      toast.add({
        severity: 'success',
        summary: 'Berhasil',
        detail: 'Notifikasi ditandai sebagai dibaca',
        life: 2000
      });
    }
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Gagal menandai notifikasi',
      life: 3000
    });
  }
}

async function markAllAsRead() {
  markingAllAsRead.value = true;
  try {
    const response = await window.axios.put('/api/notification/read-all');
    if (response.data.success) {
      notifications.value.forEach(notification => {
        notification.read_at = new Date().toISOString();
      });
      emit('notificationRead');
      toast.add({
        severity: 'success',
        summary: 'Berhasil',
        detail: 'Semua notifikasi ditandai sebagai dibaca',
        life: 2000
      });
    }
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Gagal menandai semua notifikasi',
      life: 3000
    });
  } finally {
    markingAllAsRead.value = false;
  }
}

function handleNotificationClick(notification: Notification) {
  // Mark as read if not already read
  if (!notification.read_at) {
    markAsRead(notification.id);
  }

  // Navigate to URL if exists
  if (notification.data.url) {
    visible.value = false;
    router.visit(notification.data.url);
  }
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

  if (diffInSeconds < 60) {
    return 'Baru saja';
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60);
    return `${minutes} menit yang lalu`;
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return `${hours} jam yang lalu`;
  } else if (diffInSeconds < 604800) {
    const days = Math.floor(diffInSeconds / 86400);
    return `${days} hari yang lalu`;
  } else {
    return date.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
  }
}

// Expose refresh method
defineExpose({
  loadNotifications
});
</script>
