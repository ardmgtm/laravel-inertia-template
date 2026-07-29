<template>
    <Head title="User Activity" />
    <AdminLayout title="User Activity" :breadcrumbs>
        <AppDataTableServer :handler="dtHandler" v-model:selection="selectedData" :filters="filters" dataKey="id" filter-display="row">
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
            <Column field="timestamp" header="Timestamp" class="w-40 min-w-40" :show-filter-menu="false"
                data-type="date" :show-clear-button="false">
                <template #body="slotProps">
                    <div>{{ formatDateTime(slotProps.data.timestamp) }}</div>
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <DatePicker v-model="filterModel.value" :manualInput="false" showIcon fluid @date-select="filterCallback" date-format="dd M yy"
                        :hide-on-range-selection="true" :max-date="new Date()" iconDisplay="input" class="w-40" :show-button-bar="true" @clear-click="filterCallback"/>
                </template>
            </Column>
            <Column field="status" header="Status" class="w-28 min-w-28" :show-filter-menu="false"
                :show-clear-button="false">
                <template #body="slotProps">
                    <Tag v-if="slotProps.data.status === true" severity="success" value="Success" />
                    <Tag v-else-if="slotProps.data.status === false" severity="danger" value="Failed" />
                    <Tag v-else severity="secondary" value="Unknown" />
                </template>
                <template #filter="{ filterModel, filterCallback }">
                    <Select v-model="filterModel.value" :options="statusOptions" option-value="value"
                        option-label="label" @change="filterCallback()" />
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
            <Column class="w-20 min-w-20" alignFrozen="right">
                <template #body="slotProps">
                    <Button icon="pi pi-info-circle" severity="info" text rounded size="small" 
                        @click="showDetail(slotProps.data)" v-tooltip.bottom="'View Detail'" />
                </template>
            </Column>
        </AppDataTableServer>

        <!-- Detail Drawer -->
        <Drawer v-model:visible="detailDrawer" header="Activity Detail" position="right" class="w-full md:w-[600px]">
            <div v-if="selectedActivity" class="flex flex-col gap-4">
                <!-- User Info -->
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-semibold text-primary mb-2">User Information</div>
                    <div class="flex items-center gap-3 mb-3">
                        <AppProfilePicture :user="selectedActivity.user" />
                        <div>
                            <div class="font-bold">{{ selectedActivity.user?.name ?? 'Guest' }}</div>
                            <div class="text-sm text-gray-600">{{ selectedActivity.user?.username ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="text-gray-600">IP Address:</div>
                        <div class="font-medium">{{ selectedActivity.ip_address }}</div>
                        <div class="text-gray-600">Timestamp:</div>
                        <div class="font-medium">{{ formatDateTime(selectedActivity.timestamp) }}</div>
                    </div>
                </div>

                <!-- Request Info -->
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-semibold text-primary mb-2">Request Information</div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="text-gray-600">Status:</div>
                        <div>
                            <Tag v-if="selectedActivity.status === true" severity="success" value="Success" />
                            <Tag v-else-if="selectedActivity.status === false" severity="danger" value="Failed" />
                            <Tag v-else severity="secondary" value="Unknown" />
                        </div>
                        <div class="text-gray-600">Method:</div>
                        <div><Tag :severity="getSeverityByMethod(selectedActivity.method)" :value="selectedActivity.method" /></div>
                        <div class="text-gray-600">Status Code:</div>
                        <div><Tag :severity="getSeverityByStatusCode(selectedActivity.status_code)" :value="selectedActivity.status_code" /></div>
                        <div class="text-gray-600">Route Name:</div>
                        <div class="font-medium">{{ selectedActivity.route_name ?? '-' }}</div>
                        <div class="text-gray-600">Path:</div>
                        <div class="font-medium">{{ selectedActivity.route }}</div>
                        <div class="text-gray-600">Duration:</div>
                        <div class="font-medium">{{ selectedActivity.duration_ms ? `${selectedActivity.duration_ms} ms` : '-' }}</div>
                    </div>
                </div>

                <!-- Description -->
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-semibold text-primary mb-2">Description</div>
                    <div class="text-sm">{{ selectedActivity.description ?? '-' }}</div>
                </div>

                <!-- Request Payload -->
                <div v-if="selectedActivity.request_payload" class="border rounded-lg p-4">
                    <div class="text-sm font-semibold text-primary mb-2">Request Payload</div>
                    <pre class="text-xs bg-gray-50 p-3 rounded overflow-x-auto">{{ JSON.stringify(selectedActivity.request_payload, null, 2) }}</pre>
                </div>

                <!-- Response -->
                <div v-if="selectedActivity.response" class="border rounded-lg p-4">
                    <div class="text-sm font-semibold text-primary mb-2">Response</div>
                    <pre class="text-xs bg-gray-50 p-3 rounded overflow-x-auto">{{ JSON.stringify(selectedActivity.response, null, 2) }}</pre>
                </div>

                <!-- Error Message -->
                <div v-if="selectedActivity.error_message" class="border border-red-300 bg-red-50 rounded-lg p-4">
                    <div class="text-sm font-semibold text-red-600 mb-2">Error Message</div>
                    <div class="text-sm text-red-700">{{ selectedActivity.error_message }}</div>
                </div>

                <!-- User Agent -->
                <div class="border rounded-lg p-4">
                    <div class="text-sm font-semibold text-primary mb-2">User Agent</div>
                    <div class="text-xs text-gray-700 break-words">{{ selectedActivity.user_agent }}</div>
                </div>
            </div>
        </Drawer>
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

const detailDrawer = ref(false);
const selectedActivity = ref<any>(null);

const showDetail = (activity: any) => {
    selectedActivity.value = activity;
    detailDrawer.value = true;
};

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

const statusOptions = [
    { value: null, label: 'All' },
    { value: '1', label: 'Success' },
    { value: '0', label: 'Failed' },
];

const filters: Ref<{ [key: string]: DataTableFilterMetaData }> = ref({
    '__global': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'user.name': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'ip_address': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'timestamp': { value: null, matchMode: FilterMatchMode.DATE_IS },
    'status': { value: null, matchMode: FilterMatchMode.EQUALS },
    'status_code': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'method': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'route': { value: null, matchMode: FilterMatchMode.CONTAINS },
    'description': { value: null, matchMode: FilterMatchMode.CONTAINS },
});
</script>