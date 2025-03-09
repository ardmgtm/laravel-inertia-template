<template>
    <Toast />
    <ConfirmDialog/>
    <div class="flex h-screen overflow-hidden">
        <Sidemenu :sidebarOpen="sidebarOpen" @close-sidebar="sidebarOpen = false" />
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <Header :sidebarOpen="sidebarOpen" @toggle-sidebar="sidebarOpen = !sidebarOpen" :breadcrumbs="breadcrumbs"/>
            <main class="grow">
                <Transition name="page" mode="out-in" appear>
                    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
                        <div class="flex items-start flex-col flex-1" v-if="props.title">
                            
                            <div class="flex items-end justify-between w-full">
                                <div class="font-bold text-3xl text-surface-900 dark:text-surface-0">
                                    {{ props.title }}
                                </div>
                                <div class="flex-none">
                                    <slot name="action" />
                                </div>
                            </div>
                        </div>
                        <hr class="my-4" />
                        <slot/>
                    </div>
                </Transition>
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Sidemenu from './Components/Sidemenu/Sidemenu.vue';
import Header from './Components/Header/Header.vue';
import { MenuItem } from 'primevue/menuitem';
import { Toast } from 'primevue';
import { useAuthStore } from '@/Stores/auth-store';

const authStore = useAuthStore();

const sidebarOpen = ref(false);
const props = defineProps({
    title: {
        type: String,
        required: false,
    },
    breadcrumbs: {
        type: Array as () => MenuItem[],
        required: false,
    }
})
</script>
<style scoped>
    .page-enter-from,
    .page-leave-to {
        opacity: 0;
    }

    .page-enter-active,
    .page-leave-active {
        transition: opacity 0.3s ease-out;
    }
</style>    