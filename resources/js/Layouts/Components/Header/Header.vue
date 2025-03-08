<template>
  <header
    class="sticky top-0 before:absolute before:inset-0 before:backdrop-blur-md max-lg:before:bg-white/90 dark:max-lg:before:bg-gray-800/90 before:-z-10 z-30 shadow-lg"
    :class="[
      'before:bg-white after:absolute after:h-px after:inset-x-0 after:top-full after:bg-gray-200',
    ]">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex">
          <Button class="flex items-center lg:hidden" outlined severity="secondary"
            @click.stop="$emit('toggle-sidebar')">
            <i class="pi pi-bars text-2xl"></i>
          </Button>
        </div>

        <div class="flex items-center space-x-3">
          <div>
            <Button icon="pi pi-search" size="small" label="Search" outlined severity="secondary"
              class="flex justify-start w-36" @click.stop="searchModalOpen = true" aria-controls="search-modal" />
            <SearchModal id="search-modal" searchId="search" :modalOpen="searchModalOpen"
              @open-modal="searchModalOpen = true" @close-modal="searchModalOpen = false" />
          </div>
          <Notifications align="right" />
          <!-- Divider -->
          <hr class="w-px h-6 bg-gray-200 dark:bg-gray-700/60 border-none" />
          <HeaderAccount />
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'

import SearchModal from './ModalSearch.vue'
import Notifications from './DropdownNotifications.vue'
import HeaderAccount from './HeaderAccount.vue';

const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: false,
  },
});

const searchModalOpen = ref(false)
</script>