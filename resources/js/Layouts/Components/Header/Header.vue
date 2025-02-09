<template>
    <header class="flex-none flex gap-4 fixed w-full z-50 border border-surface-300">
        <div class="flex-1 flex items-center justify-between py-2 px-4 bg-surface-50">
            <div class="flex items-center gap-4">
                <Button class="flex items-center lg:hidden" outlined severity="secondary" @click="toggleSidemenuDrawer">
                    <i class="pi pi-bars text-2xl"></i>
                </Button>
                <Link :href="route('dashboard')">
                    <img src="/vite.svg" alt="app-logo" class="h-10" />
                </Link>
            </div>
            <div class="flex flex-row gap-4 mr-2">
                <Button class="flex items-center" outlined severity="secondary">
                    <OverlayBadge :value="2" class="flex" size="small">
                        <i class="pi pi-bell text-2xl" />
                    </OverlayBadge>
                </Button>
                <HeaderAccount />
            </div>
        </div>
        <SidemenuDrawer v-model:visible="sideMenuDrawerVisible"/>
    </header>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import HeaderAccount from './HeaderAccount.vue';
import SidemenuDrawer from '@/Layouts/Components/Sidemenu/SidemenuDrawer.vue';
import { Link } from '@inertiajs/vue3';

const sideMenuDrawerVisible = ref(false);
function toggleSidemenuDrawer(){
    sideMenuDrawerVisible.value = !sideMenuDrawerVisible.value;
}
</script>