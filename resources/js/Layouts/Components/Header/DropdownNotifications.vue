<template>
  <OverlayBadge value="2" size="small">
    <Button icon="pi pi-bell" severity="secondary" variant="text" rounded @click="openNotification" />
  </OverlayBadge>
  <Popover ref="popover" class="w-80">
    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase pt-1.5 pb-2 px-4">Notifications
    </div>
    <ul ref="dropdown">
      <li class="border-b border-gray-200 dark:border-gray-700/60 last:border-0">
        <div class="block py-2 px-4 hover:bg-gray-50 dark:hover:bg-gray-700/20" to="#0">
          <span class="block text-sm mb-2">📣 <span class="font-medium text-gray-800 dark:text-gray-100">Edit your
              information in a swipe</span> Sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
            mollit anim.</span>
          <span class="block text-xs font-medium text-gray-400 dark:text-gray-500">Feb 12, 2024</span>
        </div>
      </li>
      <li class="border-b border-gray-200 dark:border-gray-700/60 last:border-0">
        <div class="block py-2 px-4 hover:bg-gray-50 dark:hover:bg-gray-700/20" to="#0">
          <span class="block text-sm mb-2">📣 <span class="font-medium text-gray-800 dark:text-gray-100">Edit your
              information in a swipe</span> Sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
            mollit anim.</span>
          <span class="block text-xs font-medium text-gray-400 dark:text-gray-500">Feb 9, 2024</span>
        </div>
      </li>
      <li class="border-b border-gray-200 dark:border-gray-700/60 last:border-0">
        <div class="block py-2 px-4 hover:bg-gray-50 dark:hover:bg-gray-700/20" to="#0">
          <span class="block text-sm mb-2">🚀<span class="font-medium text-gray-800 dark:text-gray-100">Say goodbye
              to paper receipts!</span> Sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt
            mollit anim.</span>
          <span class="block text-xs font-medium text-gray-400 dark:text-gray-500">Jan 24, 2024</span>
        </div>
      </li>
    </ul>
  </Popover>
</template>
<script setup lang="ts">
import { ref } from 'vue';

const popover = ref();

function openNotification(event: Event) {
  popover.value.toggle(event);
}
</script>