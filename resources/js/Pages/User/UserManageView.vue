<template>
    <Head title="User Manage"/>
    <AdminLayout title="User Manage" :breadcrumbs>
        <template #action>
            <Button label="Add User" icon="pi pi-plus" @click="addUserAction"/>
        </template>
    </AdminLayout>
    <UserFormModal ref="userFormModalRef"/>
</template>
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, Ref } from 'vue';
import { MenuItem } from 'primevue/menuitem';
import UserFormModal from './UserFormModal.vue';

const breadcrumbs : Ref<MenuItem[]> = ref([
    {
        label: 'User Management',
        url: route('user.browse'),
    }
])
const userFormModalRef = ref();
const addUserAction = () => userFormModalRef.value.addAction();
const editUserAction = () => userFormModalRef.value.editAction();   

</script>