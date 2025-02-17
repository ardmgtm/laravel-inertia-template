<template>

    <Head title="User Manage" />
    <AdminLayout title="User Manage" :breadcrumbs>
        <template #action>
            <Button label="Add User" icon="pi pi-plus" @click="addUserAction" />
        </template>
        <DataTable :value="dtHandler?.loadedData?.data" v-model:selection="selectedData" v-model:filters="filters"
            scrollable paginator dataKey="id" filterDisplay="row" :rows="dtHandler.size" :loading="dtHandler?.loading"
            :totalRecords="dtHandler?.loadedData?.totalRecords" :first="(dtHandler.page - 1) * dtHandler.size"
            @filter="dtHandler?.onFilter" @sort="dtHandler?.onSort" @page="dtHandler?.onPage" :lazy="true" class="mt-6">
            <template #empty>
                <div class="py-4 flex justify-center">
                    No Users Data.
                </div>
            </template>
            <template #header>
                <div class="flex justify-end">
                    <Button severity="contrast" variant="text" icon="pi pi-sync" @click="dtHandler.loadData" />
                </div>
            </template>
            <Column selectionMode="multiple" headerStyle="width: 3rem" />
            <Column field="name" header="Name" class="min-w-72" sortable>
                <template #loading>
                    <div class="flex items-center" :style="{ height: '17px', 'flex-grow': '1', overflow: 'hidden' }">
                        <Skeleton width="60%" height="1rem" />
                    </div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value" type="text" @change="filterCallback()" class="w-full" />
                </template>
            </Column>
            <Column field="username" header="Username" class="min-w-72" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value" type="text" @change="filterCallback()" class="w-full" />
                </template>
            </Column>
            <Column field="email" header="Email" class="min-w-72" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText v-model="filterModel.value" type="text" @change="filterCallback()" class="w-full" />
                </template>
            </Column>
            <Column field="is_active" header="Status" class="w-32 text-center" :showFilterMenu="false">
                <template #body="slotProps">
                    <Tag :severity="slotProps.data.is_active ? 'success' : 'danger'"
                        :value="slotProps.data.is_active ? 'Active' : 'Inactive'" />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Select v-model="filterModel.value" option-value="value" option-label="label"
                        @change="filterCallback()" :options="statusOptions" class="min-w-32">
                        <template #option="slotProps">
                            <Tag :value="slotProps.option.label" :severity="slotProps.option.severity" />
                        </template>
                    </Select>
                </template>
            </Column>
            <Column field="id" class="w-16">
                <template #body="slotProps">
                    <div class="flex gap-2">
                        <Button icon="pi pi-ellipsis-v" severity="secondary" variant="text" rounded
                            @click="(e) => { $refs.op?.toggle(e); selectedRowData = slotProps.data; }" />
                        <Popover ref="op" :key="slotProps.data.id">
                            <div class="flex flex-col gap-1 w-48">
                                <span class="font-bold">Options</span>
                                <Button icon="pi pi-pen-to-square" severity="secondary" variant="text"
                                    class="w-full flex justify-start" label="Edit Users" size="small"
                                    @click="editUserAction" />
                                <Button icon="pi pi-trash" severity="danger" variant="text"
                                    class="w-full flex justify-start" label="Delete Users" size="small"
                                    @click="deleteUserAction" />
                            </div>
                        </Popover>
                    </div>
                </template>
            </Column>
        </DataTable>
    </AdminLayout>
    <UserFormModal ref="userFormModalRef" @data-change="dtHandler.loadData" />
</template>
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref, Ref } from 'vue';
import { MenuItem } from 'primevue/menuitem';
import UserFormModal from './UserFormModal.vue';
import { DataTableFilterMetaData } from 'primevue';
import { DataTableHandler } from '@/Core/Handlers/data-table-handler';

const breadcrumbs: Ref<MenuItem[]> = ref([
    {
        label: 'User Management',
        url: route('user.browse'),
    }
])
const userFormModalRef = ref();
const addUserAction = () => userFormModalRef.value.addAction();
const editUserAction = () => userFormModalRef.value.editAction(selectedRowData.value);
const deleteUserAction = () => userFormModalRef.value.deleteAction(selectedRowData.value);

// Datatable
const selectedData = ref();
const dtHandler = ref(new DataTableHandler(route('user.data_table')));

const filters: Ref<{ [key: string]: DataTableFilterMetaData }> = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    username: { value: null, matchMode: FilterMatchMode.CONTAINS },
    email: { value: null, matchMode: FilterMatchMode.CONTAINS },
    is_active: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const selectedRowData = ref(null);

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