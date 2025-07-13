<template>
    <div class="custom-table-wrapper">
        <DataTable paginator removableSort lazy scrollable v-model:selection="selection"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink JumpToPageInput CurrentPageReport NextPageLink LastPageLink"
            :rowsPerPageOptions="[5, 10, 20, 50]" :value="handler?.loadedData?.value.data"
            :totalRecords="handler?.loadedData?.value.totalRecords" :first="(handler.page - 1) * handler.size"
            :rows="handler.size" @filter="handler?.onFilter" @sort="handler?.onSort" @page="handler?.onPage"
            v-bind="$attrs" v-on="$attrs">
            <template #empty>
                <div class="py-4 flex justify-center" v-if="!handler?.error.value"> 
                    {{ emptyMessage }}
                </div>
                <div v-else>
                    <div class="py-4 flex flex-col items-center text-red-500">
                        <i class="pi pi-exclamation-triangle text-4xl"></i>
                        <span class="mt-2">{{ errorMessage }}</span>
                    </div>
                </div>
            </template>
            <template #header>
                <div class="flex items-center h-10">
                    <slot name="header-start"></slot>
                    <div class="flex-grow"></div>
                    <slot name="header-end">
                        <Button class="py-3" severity="contrast" variant="text" icon="pi pi-sync" rounded
                            @click="handler.loadData" />
                    </slot>
                </div>
            </template>
            <slot></slot>
        </DataTable>
        <div v-if="handler?.loading.value" class="table-loading-overlay">
            <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="5" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { DataTableHandler } from '@/Core/Handlers/data-table-handler';
import { computed, onMounted, PropType } from 'vue';

const props = defineProps({
    handler: {
        type: Object as PropType<DataTableHandler>,
        required: true
    },
    selection: {
        type: Array as PropType<any[]>,
    },
    emptyMessage: {
        type: String,
        default: 'No data available.'
    },
    errorMessage: {
        type: String,
        default: 'Error processing data'
    }
});
const selection = computed({
    get: () => props.selection,
    set: (value) => emit('update:selection', value)
});

onMounted(props.handler.loadData);

const emit = defineEmits(['update:selection', 'update:filters']);
</script>
<style scoped>
.custom-table-wrapper {
    position: relative;
}

.table-loading-overlay {
    position: absolute;
    top: 120px;
    left: 0;
    width: 100%;
    height: calc(100% - 120px - 58px);
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>