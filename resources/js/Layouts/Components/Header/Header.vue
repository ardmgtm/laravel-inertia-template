<template>
  <header
    class="sticky top-0 before:absolute before:inset-0 before:backdrop-blur-md max-lg:before:bg-white/90 dark:max-lg:before:bg-gray-800/90 before:-z-10 z-30 shadow-lg"
    :class="[
      'before:bg-white after:absolute after:h-px after:inset-x-0 after:top-full after:bg-gray-200',
    ]">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <Button class="flex items-center lg:hidden" outlined severity="secondary"
            @click.stop="$emit('toggle-sidebar')">
            <i class="pi pi-bars text-2xl"></i>
          </Button>
          <Breadcrumb :home="home" :model="breadcrumbs" class="hidden md:block bg-transparent text-white" v-if="showBreadcrumbs" />
        </div>

        <div class="flex items-center space-x-3">
          <Notifications ref="notificationsRef" align="right" @open-drawer="$emit('open-notification-drawer')" />
          <!-- Divider -->
          <hr class="w-px h-6 bg-gray-200 dark:bg-gray-700/60 border-none" />
          <HeaderAccount />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

import Notifications from './DropdownNotifications.vue'
import HeaderAccount from './HeaderAccount.vue';
import { MenuItem } from 'primevue/menuitem';

const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: false,
  },
  breadcrumbs: {
    type: Array as () => MenuItem[],
    required: false,
  }
});

const home = ref({
  icon: 'pi pi-home',
  url: '/',
});
const showBreadcrumbs = computed(() => props.breadcrumbs != null && props.breadcrumbs.length > 0);

const notificationsRef = ref();

// Expose method to refresh notifications
defineExpose({
  refreshNotifications: () => {
    notificationsRef.value?.loadUnreadCount();
  }
});
</script>