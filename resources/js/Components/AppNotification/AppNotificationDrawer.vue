<template>
  <Drawer v-model:visible="visible" header="Notifikasi" position="right" class="!w-[24rem]">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h2 class="text-lg font-semibold">Notifications</h2>
        <Button v-if="notificationStore.unreadCount > 0" label="Mark All as Read" text size="small" @click="handleMarkAllAsRead"
          :loading="notificationStore.markingAllAsRead" />
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="notificationStore.loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="p-4 border rounded-lg animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
        <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
      </div>
    </div>

    <!-- Tabs -->
    <div v-else class="flex flex-col h-full">
      <Tabs v-model:value="activeTab" class="flex flex-col flex-1">
        <TabList>
          <Tab value="0" class="flex items-center gap-2">
            <span>Unread</span>
            <Badge v-if="notificationStore.unreadCount > 0" :value="notificationStore.unreadCount" severity="danger" />
              </Tab>
          <Tab value="1" class="flex items-center gap-2">
            <span>All</span>
            <Badge :value="notificationStore.allNotifications.length" severity="primary" />
          </Tab>
        </TabList>
        <!-- Tab Belum Dibaca -->
        <TabPanel value="0" class="flex flex-col h-full">
          <div class="overflow-y-auto no-scrollbar" style="max-height: calc(100vh - 250px);">
            <!-- Empty State -->
            <div v-if="notificationStore.unreadNotifications.length === 0" class="flex flex-col items-center justify-center py-12">
              <i class="pi pi-check-circle text-6xl text-gray-300 mb-4"></i>
              <p class="text-gray-500 text-center">No unread notifications</p>
            </div>

            <!-- Notification List -->
            <div v-else class="space-y-2 pr-2">
              <AppNotificationItem
                v-for="notification in notificationStore.unreadNotifications"
                :key="notification.id"
                :notification="notification"
                @click="handleNotificationClick(notification)"
                @mark-read="handleMarkAsRead(notification.id)"
              />
            </div>
          </div>
        </TabPanel>

        <!-- Tab Semua -->
        <TabPanel value="1" class="flex flex-col h-full">
          <div class="overflow-y-auto no-scrollbar" style="max-height: calc(100vh - 250px);">
            <!-- Empty State -->
            <div v-if="notificationStore.allNotifications.length === 0" class="flex flex-col items-center justify-center py-12">
              <i class="pi pi-bell text-6xl text-gray-300 mb-4"></i>
              <p class="text-gray-500 text-center">No notifications</p>
            </div>

            <!-- Notification List -->
            <div v-else class="space-y-2 pr-2">
              <AppNotificationItem
                v-for="notification in notificationStore.allNotifications"
                :key="notification.id"
                :notification="notification"
                @click="handleNotificationClick(notification)"
                @mark-read="handleMarkAsRead(notification.id)"
              />
            </div>
          </div>
        </TabPanel>
      </Tabs>

      <!-- Load More -->
      <div v-if="notificationStore.hasMore && !notificationStore.loading" class="mt-4 flex-shrink-0">
        <Button label="Muat Lebih Banyak" outlined severity="secondary" class="w-full" @click="handleLoadMore"
          :loading="notificationStore.loadingMore" />
      </div>
    </div>
  </Drawer>
</template>

<script setup lang="ts">
import { computed, watch, onMounted, ref } from 'vue';
import Notification from '@/Core/Models/notification';
import { router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useNotificationStore } from '@/Stores/notification-store';
import AppNotificationItem from './AppNotificationItem.vue';

const props = defineProps<{
  modelValue: boolean;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: boolean];
  'notificationRead': [];
}>();

const toast = useToast();
const notificationStore = useNotificationStore();
const activeTab = ref('0');

const visible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

onMounted(() => {
  if (visible.value) {
    loadNotifications();
  }
});

// Load notifications when drawer opens
watch(visible, (isVisible) => {
  if (isVisible && notificationStore.notifications.length === 0) {
    loadNotifications();
  }
});

async function loadNotifications() {
  try {
    await notificationStore.loadNotifications();
  } catch (error) {}
}

async function handleLoadMore() {
  try {
    await notificationStore.loadMore();
  } catch (error) {}
}

async function handleMarkAsRead(notificationId: string) {
  try {
    const success = await notificationStore.markAsRead(notificationId);
    if (success) {
      emit('notificationRead');
    }
  } catch (_) {}
}

async function handleMarkAllAsRead() {
  try {
    const success = await notificationStore.markAllAsRead();
    if (success) {
      emit('notificationRead');
    }
  } catch (_) {}
}

function handleNotificationClick(notification: Notification) {
  // Mark as read if not already read
  if (!notification.read_at) {
    handleMarkAsRead(notification.id);
  }

  // Navigate to URL if exists
  if (notification.data.url) {
    visible.value = false;
    router.visit(notification.data.url);
  }
}

// Expose refresh method for external use
defineExpose({
  loadNotifications
});
</script>
