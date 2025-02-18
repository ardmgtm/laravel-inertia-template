<template>
<Head title="Role Manage" />
<AdminLayout title="Role Manage" :breadcrumbs>
</AdminLayout>
</template>
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { MenuItem } from 'primevue/menuitem';
import { ref, Ref } from 'vue';

const breadcrumbs: Ref<MenuItem[]> = ref([
    {
        label: 'Role Management',
        url: route('user.browse'),
    }
])
</script>