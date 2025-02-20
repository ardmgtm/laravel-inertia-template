<template>
    <Head title="User Activity"/>
    <AdminLayout title="User Activity" :breadcrumbs>
        <AppDataTableServer :handler="dtHandler" v-model:selection="selectedData" :filters="filters" dataKey="id"
            emptyMessage="No Users Data.">
            <template #header-start>
                <div v-if="selectedData?.length > 0">
                    <div class="border border-gray-300 rounded-lg px-2 flex items-center">
                        <div class="font-bold">
                            <Button icon="pi pi-times" variant="text" severity="secondary" @click="selectedData = []"
                                rounded />
                            <span>{{ selectedData.length }} selected</span>
                        </div>
                        <Divider layout="vertical" />
                        <div>
                            <Button rounded icon="pi pi-user-plus" variant="text" severity="secondary"
                                v-tooltip.bottom="'Assign Role'" />
                            <Button rounded icon="pi pi-download" variant="text" severity="secondary"
                                v-tooltip.bottom="'Download Data'" />
                            <Button rounded icon="pi pi-trash" variant="text" severity="secondary"
                                v-tooltip.bottom="'Delete User'" />
                            <Button rounded icon="pi pi-ellipsis-v" variant="text" severity="secondary"
                                v-tooltip.bottom="'More Action'" />
                        </div>
                    </div>
                </div>
            </template>
            <Column selectionMode="multiple" headerStyle="width: 3rem" />
            <Column field="name" header="Name" class="min-w-72" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" type="text" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="username" header="Username" class="min-w-72" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" type="text" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="email" header="Email" class="min-w-72" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" type="text" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="is_active" header="Status" class="w-32 text-center" :showFilterMenu="false" sortable>
                <template #body="slotProps">
                    <Tag icon="pi pi-circle-fill" :severity="slotProps.data.is_active ? 'success' : 'danger'"
                        :value="slotProps.data.is_active ? 'Active' : 'Inactive'" />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    
                </template>
            </Column>
        </AppDataTableServer>
    </AdminLayout>
</template>
<script setup lang="ts">
import AppDataTableServer from '@/Components/AppDataTable/AppDataTableServer.vue';
import { createDataTableHandler } from '@/Core/Handlers/data-table-handler';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { FilterMatchMode } from '@primevue/core/api';

import { DataTableFilterMetaData } from 'primevue';
import { MenuItem } from 'primevue/menuitem';
import { ref, Ref } from 'vue';

const breadcrumbs: Ref<MenuItem[]> = ref([
    {
        label: 'User Activity',
        url: route('user_activity.browse'),
    }
]);

const selectedData = ref();
const dtHandler = createDataTableHandler(route('user.data_table'));
const filters: Ref<{ [key: string]: DataTableFilterMetaData }> = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    username: { value: null, matchMode: FilterMatchMode.CONTAINS },
    email: { value: null, matchMode: FilterMatchMode.CONTAINS },
    is_active: { value: null, matchMode: FilterMatchMode.EQUALS },
});
</script>