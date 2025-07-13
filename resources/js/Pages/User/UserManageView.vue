<template>
    <Head title="User Manage" />
    <AdminLayout title="User Manage" :breadcrumbs>
        <template #action>
            <Button label="Add User" icon="pi pi-plus" @click="addUserAction" v-if="can('user.create')" />
        </template>
        <AppDataTableServer :handler="dtHandler" v-model:selection="selectedData" :filters="filters" data-key="id" filter-display="row"
            empty-message="No Users Data.">
            <template #header-start>
                <Transition name="fadetransition" mode="out-in" appear>
                    <div v-if="selectedData?.length > 0">
                        <div class="border border-gray-300 rounded-lg px-2 flex items-center">
                            <div class="font-bold">
                                <Button icon="pi pi-times" variant="text" severity="secondary"
                                    @click="selectedData = []" rounded />
                                <span>{{ selectedData.length }} selected</span>
                            </div>
                            <Divider layout="vertical" class="bg-gray-800" />
                            <div>
                                <Button rounded icon="pi pi-check-circle" variant="text" severity="secondary"
                                    @click="switchStatusAction(selectedData, true)" v-tooltip.bottom="'Enable User'" />
                                <Button rounded icon="pi pi-times-circle" variant="text" severity="secondary"
                                    @click="switchStatusAction(selectedData, false)"
                                    v-tooltip.bottom="'Disable User'" />
                                <Button rounded icon="pi pi-ellipsis-v" variant="text" severity="secondary"
                                    v-tooltip.bottom="'More Action'" />
                            </div>
                        </div>
                    </div>
                </Transition>
            </template>
            <Column field="id" selectionMode="multiple" headerStyle="width: 3rem" />
            <Column field="name" header="Name" class="min-w-72" :show-clear-button="false" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" type="text" @change="filterCallback()" fluid />
                </template>
                <template #body="slotProps">
                    <div class="flex gap-4">
                        <AppProfilePicture :user="(slotProps.data as User)" />
                        <div>{{ slotProps.data.name }}</div>
                    </div>
                </template>
            </Column>
            <Column field="username" header="Username" class="min-w-72" :show-clear-button="false" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" type="text" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="email" header="Email" class="min-w-72" :show-clear-button="false" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" type="text" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="roles.id" header="Role" class="min-w-32 w-32" :showFilterMenu="false"
                :show-clear-button="false">
                <template #body="slotProps">
                    <div class="flex flex-wrap gap-2">
                        <AppColorTag v-for="role in slotProps.data.roles" :key="role.id" :label="role.name" />
                    </div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <MultiSelect size="small" v-model="filterModel.value" option-value="id" option-label="name"
                        @change="filterCallback()" :options="roleOptions" class="min-w-32">
                        <template #option="slotProps">
                            <AppColorTag :label="slotProps.option.name" />
                        </template>
                    </MultiSelect>
                </template>
            </Column>
            <Column field="is_active" header="Status" class="w-32 text-center" :showFilterMenu="false"
                :show-clear-button="false">
                <template #body="slotProps">
                    <Tag icon="pi pi-circle-fill" :severity="slotProps.data.is_active ? 'success' : 'danger'"
                        :value="slotProps.data.is_active ? 'Active' : 'Inactive'" />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Select size="small" v-model="filterModel.value" option-value="value" option-label="label"
                        :show-clear="true" @change="filterCallback()" :options="statusOptions" class="min-w-24">
                        <template #option="slotProps">
                            <Tag icon="pi pi-circle-fill" :value="slotProps.option.label"
                                :severity="slotProps.option.severity" />
                        </template>
                    </Select>
                </template>
            </Column>
            <Column field="id" class="w-16" v-if="can(['user.update', 'user.delete'])">
                <template #body="slotProps">
                    <div class="flex gap-2">
                        <Button icon="pi pi-ellipsis-v" severity="secondary" variant="text" rounded
                            @click="(e) => { ($refs.op as any).toggle(e); selectedRowData = slotProps.data; }" />
                    </div>
                </template>
            </Column>
        </AppDataTableServer>
        <Popover ref="op">
            <div class="flex flex-col gap-1 w-48" @click="($refs.op as any).hide()">
                <span class="font-bold">Options</span>
                <Button icon="pi pi-pen-to-square" severity="secondary" variant="text" class="w-full flex justify-start"
                    label="Edit User" size="small" @click="editUserAction" v-if="can('user.update')" />
                <Button icon="pi pi-check-circle" severity="secondary" variant="text" class="w-full flex justify-start"
                    label="Enable User" size="small" @click="switchStatusAction([selectedRowData], true)"
                    v-if="can('user.update') && (selectedRowData?.is_active == false)" />
                <Button icon="pi pi-times-circle" severity="secondary" variant="text" class="w-full flex justify-start"
                    label="Disable User" size="small" @click="switchStatusAction([selectedRowData], false)"
                    v-if="can('user.update') && (selectedRowData?.is_active == true)" />
                <Button icon="pi pi-trash" severity="danger" variant="text" class="w-full flex justify-start"
                    label="Delete User" size="small" @click="deleteUserAction" v-if="can('user.delete')" />
            </div>
        </Popover>
    </AdminLayout>
    <UserFormModal ref="userFormModalRef" @data-created="refreshData" @data-updated="refreshData"
        @data-deleted="refreshData" />
</template>
<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';
import { ref, Ref } from 'vue';
import { MenuItem } from 'primevue/menuitem';
import UserFormModal from './Components/UserFormModal.vue';
import { DataTableFilterMetaData, useToast } from 'primevue';
import { createDataTableHandler } from '@/Core/Handlers/data-table-handler';
import AppDataTableServer from '@/Components/AppDataTable/AppDataTableServer.vue';
import { FormModalExpose } from '@/Core/Models/form-modal';
import { User } from '@/Core/Models/user';
import { UserRole } from '@/Core/Models/user-role';
import AppColorTag from '@/Components/AppColorTag.vue';
import { can } from '@/Core/Utiils/permission-check';
import axios from 'axios';
import AppProfilePicture from '@/Components/AppProfilePicture.vue';

const toast = useToast();

const breadcrumbs: Ref<MenuItem[]> = ref([
    {
        label: 'User Management',
        url: route('user.browse'),
    }
])

const roleOptions = ref<UserRole[]>(usePage().props.roles as UserRole[]);

const userFormModalRef = ref<FormModalExpose<User>>();
const selectedRowData = ref<User>();

const refreshData = () => {
    dtHandler.loadData();
    selectedData.value = null;
}

const addUserAction = () => userFormModalRef.value?.addAction();
const editUserAction = () => {
    if (selectedRowData.value) {
        userFormModalRef.value?.editAction(selectedRowData.value);
    }
};
const deleteUserAction = () => {
    if (selectedRowData.value) {
        userFormModalRef.value?.deleteAction(selectedRowData.value);
    }
};
const switchStatusAction = (users: User[], status: boolean) => {
    const userIds = users.map(user => user.id);

    axios.post(route('user.switch_status'), {
        ids: userIds,
        status: status,
    })
        .then((response) => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: response.data.message,
                life: 3000,
            });
            refreshData();
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                summary: 'Failed',
                detail: error.response.data.message,
                life: 3000,
            });
        });
}

// Datatable
const selectedData = ref();
const dtHandler = createDataTableHandler(route('user.data_table'));

const filters: Ref<{ [key: string]: DataTableFilterMetaData }> = ref({
    '__global': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'name': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'username': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'email': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'roles.id': { value: null, matchMode: FilterMatchMode.EQUALS },
    'is_active': { value: null, matchMode: FilterMatchMode.EQUALS },
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