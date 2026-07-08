<template>
  <Toast position="bottom-right" group="app-notifications">
    <template #container="{ message, closeCallback }">
      <div
        class="w-96 rounded-xl border border-gray-200 bg-white p-5 shadow-2xl transition-all duration-300">
        <div class="mb-3 flex items-start justify-between">
          <div class="flex items-center gap-2">
            <div class="rounded-full bg-blue-100 p-1.5 text-blue-600">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                </path>
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-800">Notifikasi Baru</h3>
              <span class="text-xs text-gray-500">10 menit yang lalu</span>
            </div>
          </div>
    
          <Button icon="pi pi-times" severity="secondary" class="p-1" @click="closeCallback"/>
        </div>
    
        <div class="mb-4 px-1 text-sm text-gray-600">
          <p>{{ message.detail }}</p>
        </div>
    
        <div class="mt-2">
          <Button label="Buka" severity="primary" class="w-full" as="a" href="#"/>
        </div>
      </div>
    </template>
  </Toast>
</template>
<script setup lang="ts">
import { useAuthStore } from '@/Stores/auth-store';
import { useEchoModel } from '@laravel/echo-vue';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted } from 'vue';

const emit = defineEmits<{
  'notification-received': [];
}>();

const authStore = useAuthStore()
const toast = useToast();

const userId = computed(() => authStore.user?.id);
let echoChannel: any = null;

onMounted(() => {
    if (userId.value) {
        const { channel } = useEchoModel('App.Models.User', userId.value);
        echoChannel = channel();

        echoChannel.notification((notification: any) => {
            toast.add({
                severity: 'custom',
                summary: notification.title || 'New Notification',
                detail: notification.message || 'You have a new notification',
                group: 'app-notifications',
                life: 10000,
            } as any);
            
            // Emit event to parent to refresh notification count
            emit('notification-received');
        });
    }
});
</script>