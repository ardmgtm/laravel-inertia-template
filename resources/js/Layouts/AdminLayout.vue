<template>
    <div class="flex flex-row h-screen w-full bg-surface-200">
        <Header />
        <Sidemenu />
        <div class="flex-1 flex flex-col h-full">
            <div
                class="mx-2 px-8 py-6 lg:py-8 lg:px-12 flex-1 border border-surface-300 rounded-t-lg h-full bg-surface-50 mt-[72px] overflow-y-scroll app-scrollbar">
                <div class="flex items-start flex-col flex-1" v-if="props.title">
                    <Breadcrumb :home="home" :model="breadcrumbs" class="bg-surface-50 p-0 mb-4"
                        v-if="showBreadcrumbs" />
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
                <slot />
            </div>
        </div>
        <Toast/>
    </div>
</template>

<script setup lang="ts">
import Sidemenu from '@/Layouts/Components/Sidemenu/Sidemenu.vue';
import Header from '@/Layouts/Components/Header/Header.vue';
import { MenuItem } from "primevue/menuitem";
import { computed, ref } from 'vue';

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

const home = ref({
    icon: 'pi pi-home',
    href: '/',
});
const showBreadcrumbs = computed(() => props.breadcrumbs != null && props.breadcrumbs.length > 0);
</script>
