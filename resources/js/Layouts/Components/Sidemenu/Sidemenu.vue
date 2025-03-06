<template>
  <div class="min-w-fit">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 bg-gray-900/30 z-40 lg:z-auto transition-opacity duration-200"
      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true"></div>

    <!-- Sidebar -->
    <div id="sidebar" ref="sidebar"
      class="flex lg:flex! flex-col absolute z-40 left-0 top-0 lg:static lg:left-auto lg:top-auto lg:translate-x-0 h-[100dvh] overflow-y-scroll 
      lg:overflow-y-auto no-scrollbar w-64 lg:sidebar-expanded:!w-64 2xl:w-64! shrink-0 bg-surface-50 p-4 transition-all duration-200 ease-in-out 
      rounded-tr-2xl shadow-xs border border-gray-200 shadow-lg bg-[url('/images/pattern.png')] bg-contain bg-left-bottom bg-no-repeat"
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-64']">

      <!-- Sidebar header -->
      <div class="flex justify-between mb-10 pr-3 sm:px-2">
        <!-- Close button -->
        <button ref="trigger" class="lg:hidden text-gray-500 hover:text-gray-400" @click.stop="$emit('close-sidebar')"
          aria-controls="sidebar" :aria-expanded="sidebarOpen">
          <span class="sr-only">Close sidebar</span>
          <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.7 18.7l1.4-1.4L7.8 13H20v-2H7.8l4.3-4.3-1.4-1.4L4 12z" />
          </svg>
        </button>
        <!-- Logo -->
        <Link :href="route('dashboard')"><img src="/vite.svg" alt="app-logo" class="h-10" /></Link>
      </div>

      <div>
        <slot name="sidebar-header" />
      </div>

      <!-- Links -->
      <div class="space-y-8">
        <!-- Pages group -->
        <div>
          <nav class="flex-1 overflow-y-scroll no-scrollbar">
            <ul>
              <li v-for="menuItem in sideMenuItemData">
                <SidemenuItem :label="menuItem.label" :sparator="menuItem.separator" :icon="menuItem.icon"
                  :url="menuItem.url" :items="menuItem.items" v-if="can(menuItem.permissions)"/>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

import SidemenuItem from './SidemenuItem.vue';
import { sideMenuItemData } from '@/Core/Configs/sidemenu-item';
import { Link } from '@inertiajs/vue3';
import { can } from '@/Core/Utiils/permission-check';

const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits();

const trigger = ref(null)
const sidebar = ref(null)

const sidebarExpanded = ref(true)

const clickHandler = ({ target }) => {
  if (!sidebar.value || !trigger.value) return
  if (
    !props.sidebarOpen ||
    sidebar.value.contains(target) ||
    trigger.value.contains(target)
  ) return
  emit('close-sidebar')
}

// close if the esc key is pressed
const keyHandler = ({ keyCode }) => {
  if (!props.sidebarOpen || keyCode !== 27) return
  emit('close-sidebar')
}

onMounted(() => {
  document.addEventListener('click', clickHandler)
  document.addEventListener('keydown', keyHandler)
})

onUnmounted(() => {
  document.removeEventListener('click', clickHandler)
  document.removeEventListener('keydown', keyHandler)
})

watch(sidebarExpanded, () => {
  localStorage.setItem('sidebar-expanded', sidebarExpanded.value)
  if (sidebarExpanded.value) {
    document.querySelector('body').classList.add('sidebar-expanded')
  } else {
    document.querySelector('body').classList.remove('sidebar-expanded')
  }
})
</script>