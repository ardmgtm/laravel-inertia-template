<template>
    <Head title="Dashboard"/>
    <AdminLayout title="Dashboard" :breadcrumbs="breadcrumbs">
        <h1>Hello world</h1>
    </AdminLayout>
</template>
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MenuItem } from 'primevue/menuitem';
import { ref, Ref } from 'vue';

const breadcrumbs : Ref<MenuItem[]> = ref([
    { label: 'Dashboard', url: '/dashboard' },
]);

</script>