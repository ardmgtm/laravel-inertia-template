<template>
  <div class="min-w-fit relative">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 bg-gray-900/30 z-40 lg:z-auto transition-opacity duration-200"
      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true"></div>

    <!-- Toggle Button (Desktop only) - Floating on the right edge -->
    <Button 
      @click="toggleSidebar" 
      :icon="sidebarExpanded ? 'pi pi-angle-left' : 'pi pi-angle-right'" 
      severity="secondary" 
      rounded
      size="small"
      v-tooltip.right="sidebarExpanded ? 'Collapse Sidebar' : 'Expand Sidebar'"
      class="hidden lg:flex absolute top-[12px] bg-white border-2 border-gray-300 shadow-lg hover:shadow-xl hover:scale-110 hover:border-primary transition-all duration-300 z-[60] -translate-x-1/2"
      :class="sidebarExpanded ? 'left-64' : 'left-20'"
    />

    <!-- Sidebar -->
    <div id="sidebar" ref="sidebar" class="flex lg:flex! flex-col absolute z-40 left-0 top-0 lg:static lg:left-auto lg:top-auto lg:translate-x-0 h-[100dvh] overflow-y-scroll 
      lg:overflow-y-auto no-scrollbar shrink-0 bg-surface-50 px-4 pt-3 pb-4 transition-all duration-300 ease-in-out 
      shadow-xs border-r border-gray-200 shadow-lg"
      :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-64',
        sidebarExpanded ? 'w-64' : 'w-20 lg:w-20'
      ]">

      <!-- Sidebar header with Logo -->
      <div class="flex items-center mb-6 min-h-[10px] transition-all duration-300"
        :class="sidebarExpanded ? 'justify-between pr-3 sm:px-2' : 'justify-center'">
        <!-- Close button (mobile) -->
        <Button v-if="sidebarExpanded" class="lg:hidden" severity="secondary" icon="pi pi-arrow-left" variant="text" rounded
          @click.stop="$emit('close-sidebar')" aria-controls="sidebar" :aria-expanded="sidebarOpen" />
        
        <!-- Logo - Expanded Mode (Full logo with text) -->
        <Link 
          :href="route('dashboard')" 
          v-if="sidebarExpanded" 
          class="flex items-center gap-3 hover:opacity-80 transition-opacity duration-200">
          <img src="/vite.svg" alt="app-logo" class="h-7 w-auto" />
        </Link>
        
        <!-- Logo - Collapsed Mode (Icon only) -->
        <Link 
          :href="route('dashboard')" 
          v-else 
          class="flex items-center justify-center group">
          <img src="/vite.svg" alt="app-logo" class="h-auto w-7" />
        </Link>
      </div>

      <div>
        <slot name="sidebar-header" />
      </div>

      <!-- Links -->
      <div class="space-y-2">
        <!-- Pages group -->
        <div>
          <nav class="flex-1 overflow-y-scroll no-scrollbar">
            <ul>
              <li v-for="menuItem in sideMenuItemData">
                <SidemenuItem 
                  :label="menuItem.label" 
                  :sparator="menuItem.separator" 
                  :icon="menuItem.icon"
                  :url="menuItem.url" 
                  :items="menuItem.items" 
                  :collapsed="!sidebarExpanded"
                  v-if="can(menuItem.permissions)" 
                />
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
import { can } from '@/Core/Utils/permission-check.js';

const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits();

const trigger = ref(null)
const sidebar = ref(null)

const sidebarExpanded = ref(localStorage.getItem('sidebar-expanded') === 'false' ? false : true)

const toggleSidebar = () => {
  sidebarExpanded.value = !sidebarExpanded.value
}

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