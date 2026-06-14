<template>
    <Head title="Dashboard"/>
    <AdminLayout title="Dashboard" :breadcrumbs="breadcrumbs">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 mb-8 gap-6">
            <UserCard />
            <UserCard />
            <UserCard />
            <UserCard />
        </div>
        
        <!-- Charts and Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Chart1 />
            <Table1 />
        </div>
    </AdminLayout>
</template>
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { MenuItem } from 'primevue/menuitem';
import { ref, Ref } from 'vue';
import UserCard from './Components/UserCard.vue';
import Chart1 from './Components/Chart1.vue';
import Table1 from './Components/Table1.vue';

const breadcrumbs : Ref<MenuItem[]> = ref([
    { label: 'Dashboard', url: '/dashboard' },
]);

</script>