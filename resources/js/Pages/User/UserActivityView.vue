<template>
    <Head title="User Activity"/>
    <AdminLayout title="User Activity" :breadcrumbs>
        <AppDataTableServer :handler="dtHandler" v-model:selection="selectedData" dataKey="id"
            emptyMessage="No Data">
            <Column selectionMode="multiple" headerStyle="width: 3rem" />
            <Column field="user.name" header="User" class="w-72">
                <template #body="slotProps">
                    <div class="flex flex-row gap-4 items-center">
                        <AppAvatarLetter :name="slotProps.data.user?.name ?? '?'"/>
                        <div class="flex flex-col">
                            <div class="font-bold">{{ slotProps.data.user?.name ?? 'Guest' }}</div>
                            <div class="text-xs italic">{{ slotProps.data.user?.username }}</div>
                        </div>
                    </div>
                </template>
            </Column>
            <Column field="ip_address" header="IP Address" class="w-48"></Column>
            <Column field="timestamp" header="Timestamp" class="w-48"></Column>
            <Column field="status_code" header="Status Code" class="w-24">
                <template #body="slotProps">
                    <Tag :severity="getSeverityByStatusCode(slotProps.data.status_code)" :value="slotProps.data.status_code"/>
                </template>
            </Column>
            <Column field="method" header="Method" class="w-24">
                <template #body="slotProps">
                    <Tag :severity="getSeverityByMethod(slotProps.data.method)" :value="slotProps.data.method"/>
                </template>
            </Column>
            <Column field="route" header="Path" class="min-w-72"></Column>
        </AppDataTableServer>
    </AdminLayout>
</template>
<script setup lang="ts">
import AppAvatarLetter from '@/Components/AppAvatarLetter.vue';
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
const dtHandler = createDataTableHandler(route('user_activity.data_table'));

const severityMethod : any = {
  "GET": "success",
  "POST": "info",
  "PUT": "warn",
  "PATCH": "warn",
  "DELETE": "error"
}
const getSeverityByMethod = (method: string) : string => severityMethod[method];

const getSeverityByStatusCode = (statusCode : number) : string => {
    if (statusCode >= 200 && statusCode < 300) return "success";  
    if (statusCode >= 300 && statusCode < 400) return "info";     
    if (statusCode >= 400 && statusCode < 500) return "warn";     
    if (statusCode >= 500) return "error";                        
    return "info";
};

</script>