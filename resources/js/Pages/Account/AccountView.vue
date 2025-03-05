<template>

    <Head title="Account" />
    <AdminLayout title="Account" :breadcrumbs>
        <div class="flex flex-row mt-4">
            <div class="flex-none">
                <ul class="w-48 flex flex-col gap-2">
                    <li v-for="item in menu" :key="item.url">
                        <Button fluid :label="(item.label as string)"
                            :variant="activeMenu == item.key ? undefined : 'text'"
                            :severity="activeMenu == item.key ? undefined : 'secondary'" :icon="item.icon"
                            class="flex items-start justify-start" @click="() => activeMenu = (item.key as string)" />
                    </li>
                </ul>
            </div>
            <div class="flex-none">
                <Divider layout="vertical" />
            </div>
            <div class="flex-1">
                <Transition name="fadetransition" mode="out-in" appear>
                    <div v-if="activeMenu == 'basic_information'">
                        <BasicInformation />
                    </div>
                    <div v-else-if="activeMenu == 'change_password'">
                        <div>Change Password</div>
                    </div>
                    <div v-else-if="activeMenu == 'preference'">
                        <div>Preference</div>
                    </div>
                </Transition>
            </div>
        </div>
    </AdminLayout>
</template>
<script setup lang="ts">

import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import BasicInformation from './Components/BasicInformation.vue';
import { MenuItem } from 'primevue/menuitem';

const breadcrumbs = ref<MenuItem[]>([
    { label: 'Account', url: '/account' },
]);

const activeMenu = ref<string>('basic_information');
const menu = ref<MenuItem[]>([
    { key: "basic_information", label: 'Basic Information', icon: 'pi pi-user', url: '/account' },
    { key: "change_password", label: 'Change Password', icon: 'pi pi-key', url: '/account/change-password' },
    { key: "preference", label: 'Preference', icon: 'pi pi-cog', url: '/account/preference' },
]);

</script>