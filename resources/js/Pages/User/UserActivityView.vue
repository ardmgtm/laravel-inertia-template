<template>

    <Head title="User Activity" />
    <AdminLayout title="User Activity" :breadcrumbs>
        <AppDataTableServer :handler="dtHandler" v-model:selection="selectedData" :filters="filters" dataKey="id">
            <Column field="user.name" header="User" class="w-60 min-w-60" :show-filter-menu="false"
                :show-clear-button="false">
                <template #body="slotProps">
                    <div class="flex flex-row gap-4 items-center">
                        <AppProfilePicture :user="slotProps.data.user"/>
                        <div class="flex flex-col">
                            <div class="font-bold">{{ slotProps.data.user?.name ?? 'Guest' }}</div>
                            <div class="text-xs italic">{{ slotProps.data.user?.username }}</div>
                        </div>
                    </div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="ip_address" header="IP Address" class="w-36 min-w-36" :show-filter-menu="false"
                :show-clear-button="false">
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="timestamp" header="Timestamp" class="w-40 min-w-40" :show-filter-menu="false"
                data-type="date" :show-clear-button="false">
                <template #body="slotProps">
                    <div>{{ formatDateTime(slotProps.data.timestamp) }}</div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <DatePicker v-model="filterModel.value" :manualInput="false" showIcon fluid @date-select="filterCallback" date-format="dd M yy"
                        :hide-on-range-selection="true" :max-date="new Date()" iconDisplay="input" class="w-40"/>
                </template>
            </Column>
            <Column field="status_code" header="Status" class="w-24 min-w-24" :show-filter-menu="false"
                :show-clear-button="false">
                <template #body="slotProps">
                    <Tag :severity="getSeverityByStatusCode(slotProps.data.status_code)"
                        :value="slotProps.data.status_code" />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" type="text" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="method" header="Method" class="w-24" :show-filter-menu="false" :show-clear-button="false">
                <template #body="slotProps">
                    <Tag :severity="getSeverityByMethod(slotProps.data.method)" :value="slotProps.data.method" />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Select v-model="filterModel.value" :options="methodOptions" option-value="value"
                        option-label="label" @change="filterCallback()">
                        <template #option="slotProps">
                            <Tag :value="slotProps.option.label"
                                :severity="getSeverityByMethod(slotProps.option.value)" />
                        </template>
                    </Select>
                </template>
            </Column>
            <Column field="route" header="Path" class="min-w-60" :show-filter-menu="false" :show-clear-button="false">
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" @change="filterCallback()" fluid />
                </template>
            </Column>
            <Column field="description" header="Description" class="min-w-72" :show-filter-menu="false"
                :show-clear-button="false">
                <template #filter="{ filterModel, filterCallback }">
                    <InputText size="small" v-model="filterModel.value" @change="filterCallback()" fluid />
                </template>
            </Column>
        </AppDataTableServer>
    </AdminLayout>
</template>
<script setup lang="ts">
import AppProfilePicture from '@/Components/AppProfilePicture.vue';
import AppDataTableServer from '@/Components/AppDataTable/AppDataTableServer.vue';
import { createDataTableHandler } from '@/Core/Handlers/data-table-handler';
import { formatDateTime } from '@/Core/Utils/datetime-util';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

import { MenuItem } from 'primevue/menuitem';
import { ref, Ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { DataTableFilterMetaData } from 'primevue';

const breadcrumbs: Ref<MenuItem[]> = ref([
    {
        label: 'User Activity',
        url: route('user_activity.browse'),
    }
]);

const selectedData = ref();
const dtHandler = createDataTableHandler(route('user_activity.data_table'));

const severityMethod: {[key: string]: string} = {
    "GET": "success",
    "POST": "info",
    "PUT": "warn",
    "PATCH": "warn",
    "DELETE": "danger",
}
const getSeverityByMethod = (method: string): string => severityMethod[method];
const methodOptions = [
    {
        value: null,
        label: 'ALL',
    },
    {
        value: 'GET',
        label: 'GET',
    },
    {
        value: 'POST',
        label: 'POST',
    },
    {
        value: 'PUT',
        label: 'PUT',
    },
    {
        value: 'PATCH',
        label: 'PATCH',
    },
    {
        value: 'DELETE',
        label: 'DELETE',
    },
];

const getSeverityByStatusCode = (statusCode: number): string => {
    if (statusCode >= 200 && statusCode < 300) return "success";
    if (statusCode >= 300 && statusCode < 400) return "info";
    if (statusCode >= 400 && statusCode < 500) return "warn";
    if (statusCode >= 500) return "danger";
    return "info";
};

const filters: Ref<{ [key: string]: DataTableFilterMetaData }> = ref({
    '__global': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'user.name': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'ip_address': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'timestamp': { value: null, matchMode: FilterMatchMode.DATE_IS },
    'status_code': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'method': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'route': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'description': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>