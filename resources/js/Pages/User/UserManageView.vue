<template>
    <Head title="User Manage"/>
    <AdminLayout title="User Manage" :breadcrumbs>
        <template #action>
            <Button label="Add User" icon="pi pi-plus" @click="addUserAction"/>
        </template>
        <DataTable 
            :value="dataLoads" v-model:selection="selectedData" v-model:filters="filters"
            scrollable  paginator :rows="10" dataKey="id" filterDisplay="row"
            class="mt-6">
            <template #empty>
                <div class="py-4 flex justify-center">
                    No Users Data. 
                </div>
            </template>
            <template #header>
                <div class="flex justify-end">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="filters['global'].value" placeholder="Search" />
                    </IconField>
                </div>
            </template>
            <Column selectionMode="multiple" headerStyle="width: 3rem" />
            <Column field="name" header="Name" class="min-w-72" frozen sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value" type="text" @input="filterCallback()" class="w-full"/>
                </template>
            </Column>
            <Column field="username" header="Username" class="min-w-72" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value" type="text" @input="filterCallback()" class="w-full"/>
                </template>
            </Column>
            <Column field="email" header="Email" class="min-w-72" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value" type="text" @input="filterCallback()" class="w-full"/>
                </template>
            </Column>
            <Column field="is_active" header="Status" class="w-32 text-center" :showFilterMenu="false">
                <template #body="slotProps">
                    <Tag 
                        :severity="slotProps.data.is_active ? 'success' : 'danger'" 
                        :value="slotProps.data.is_active ? 'Active' : 'Inactive'"/>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Select v-model="filterModel.value" option-value="value" option-label="label" @change="filterCallback()" :options="statusOptions" class="min-w-32">
                        <template #option="slotProps">
                            <Tag :value="slotProps.option.label" :severity="slotProps.option.severity"/>
                        </template>
                    </Select>
                </template>
            </Column>
            <Column class="w-16">
                <template #body="slotProps">
                    <div class="flex gap-2">
                        <Button icon="pi pi-ellipsis-v" severity="secondary" variant="text" rounded />
                        
                    </div>
                </template>
            </Column>
        </DataTable>
    </AdminLayout>
    <UserFormModal ref="userFormModalRef"/>
</template>
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';
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

const dataLoads = ref([
    {
        id : 1,
        name : 'Administrator',
        username : 'admin',
        email : 'admin@mail.com',
        is_active : true,
    },
    {
        id: 2,
        name: 'John Doe',
        username: 'johndoe',
        email: 'johndoe@mail.com',
        is_active : true,
    },
    {
        id: 3,
        name: 'Jane Smith',
        username: 'janesmith',
        email: 'janesmith@mail.com',
        is_active : true,
    },
    {
        id: 4,
        name: 'Alice Johnson',
        username: 'alicejohnson',
        email: 'alicejohnson@mail.com',
        is_active : true,
    },
    {
        id: 5,
        name: 'Bob Brown',
        username: 'bobbrown',
        email: 'bobbrown@mail.com',
        is_active : true,
    },
    {
        id: 6,
        name: 'Charlie Davis',
        username: 'charliedavis',
        email: 'charliedavis@mail.com',
        is_active : true,
    },
    {
        id: 7,
        name: 'David Evans',
        username: 'davidevans',
        email: 'davidevans@mail.com',
        is_active : true,
    },
    {
        id: 8,
        name: 'Eve Foster',
        username: 'evefoster',
        email: 'evefoster@mail.com',
        is_active : true,
    },
    {
        id: 9,
        name: 'Frank Green',
        username: 'frankgreen',
        email: 'frankgreen@mail.com',
        is_active : true,
    },
    {
        id: 10,
        name: 'Grace Harris',
        username: 'graceharris',
        email: 'graceharris@mail.com',
        is_active : true,
    },
    {
        id: 11,
        name: 'Hank Irving',
        username: 'hankirving',
        email: 'hankirving@mail.com',
        is_active : true,
    },
    {
        id: 12,
        name: 'Ivy Johnson',
        username: 'ivyjohnson',
        email: 'ivyjohnson@mail.com',
        is_active : true,
    },
    {
        id: 13,
        name: 'Jack King',
        username: 'jackking',
        email: 'jackking@mail.com',
        is_active : true,
    },
    {
        id: 14,
        name: 'Karen Lee',
        username: 'karenlee',
        email: 'karenlee@mail.com',
        is_active : true,
    },
    {
        id: 15,
        name: 'Larry Moore',
        username: 'larrymoore',
        email: 'larrymoore@mail.com',
        is_active : true,
    },
    {
        id: 16,
        name: 'Mona Nelson',
        username: 'monanelson',
        email: 'monanelson@mail.com',
        is_active : true,
    },
    {
        id: 17,
        name: 'Nina Owens',
        username: 'ninaowens',
        email: 'ninaowens@mail.com',
        is_active : true,
    },
    {
        id: 18,
        name: 'Oscar Perez',
        username: 'oscarperez',
        email: 'oscarperez@mail.com',
        is_active : true,
    },
    {
        id: 19,
        name: 'Paul Quinn',
        username: 'paulquinn',
        email: 'paulquinn@mail.com',
        is_active : true,
    },
    {
        id: 20,
        name: 'Rachel Scott',
        username: 'rachelscott',
        email: 'rachelscott@mail.com',
        is_active : true,
    }
]);
const selectedData = ref();
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    username: { value: null, matchMode: FilterMatchMode.CONTAINS },
    email: { value: null, matchMode: FilterMatchMode.CONTAINS },
    is_active: { value: null, matchMode: FilterMatchMode.EQUALS },
});
const statusOptions = ref([
    {
        value: false,
        label: 'Inactive',
        severity: 'danger',
    },
    {
        value: true,
        label: 'Active',
        severity: 'success',
    }
])
</script>