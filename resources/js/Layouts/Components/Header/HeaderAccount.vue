<template>
    <Button outlined rounded class="p-1" @click="toggle">
        <AppProfilePicture :user="user" />
    </Button>
    <Menu ref="menu" :model="accountMenuItems" class="w-full md:w-60 m-2" id="overlay_menu" :popup="true">
        <template #start>
            <button v-ripple
                class="relative overflow-hidden w-full  border-0 bg-transparent flex items-center gap-2 p-2 pl-4 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-none cursor-pointer transition-colors duration-200">
                <AppProfilePicture :user="user" class="flex-none" />
                <span class="inline-flex flex-col items-start">
                    <span class="font-bold text-left">{{ user?.name }}</span>
                    <span class="text-sm">{{ user?.username }}</span>
                </span>
            </button>
        </template>
        <template #item="{ item, props }">
            <a v-ripple class="flex items-center" v-bind="props.action">
                <span :class="item.icon" />
                <span>{{ item.label }}</span>
                <Badge v-if="item.badge" class="ml-auto" :value="item.badge" />
                <span v-if="item.shortcut"
                    class="ml-auto border border-surface rounded bg-emphasis text-muted-color text-xs p-1">
                    {{ item.shortcut }}
                </span>
            </a>
        </template>
    </Menu>
</template>
<script setup lang="ts">
import { computed, Ref, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { MenuItem } from 'primevue/menuitem';
import { User } from '@/Core/Models/user';
import AppProfilePicture from '@/Components/AppProfilePicture.vue';
import { useAuthStore } from '@/Stores/auth-store';

const menu = ref();
const authStore = useAuthStore();
const user: Ref<User | null> = computed(() => authStore.user);

const accountMenuItems: Ref<MenuItem[]> = ref([
    {
        items: [
            {
                label: 'Account',
                icon: 'pi pi-user',
                command: () => router.get(route('account.browse'))
            },
            {
                label: 'Sign Out',
                icon: 'pi pi-sign-out',
                command: () => logoutAction(),
            }
        ]
    }
]);

function toggle(event: Event) {
    menu.value.toggle(event);
};

function logoutAction() {
    router.post(route('logout'), {}, {
        onSuccess: (response: any) => authStore.$reset()
    });
}
</script>