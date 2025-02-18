<template>
    <DataTable 
        scrollable 
        paginator
        v-model:selection="selection"
        :lazy="true"
        :value="handler?.loadedData?.value.data" 
        :loading="handler?.loading.value"
        :totalRecords="handler?.loadedData?.value.totalRecords"
        :first="(handler.page - 1) * handler.size"
        :rows="handler.size"
        filterDisplay="row"
        @filter="handler?.onFilter" 
        @sort="handler?.onSort" 
        @page="handler?.onPage"
        v-bind="$attrs"
        v-on="$attrs"
    >
        <template #empty>
            <div class="py-4 flex justify-center">
                {{ emptyMessage }}
            </div>
        </template>
        <template #header>
            <div class="flex items-center">
                <slot name="header-start"></slot>
                <div class="flex-grow"></div>
                <slot name="header-end">
                    <Button 
                        class="py-3"
                        severity="contrast" 
                        variant="text" 
                        icon="pi pi-sync" 
                        @click="handler.loadData" 
                    />
                </slot>
            </div>
        </template>
        <slot></slot>
    </DataTable>
</template>

<script setup lang="ts">
import { DataTableHandler } from '@/Core/Handlers/data-table-handler';
import { computed, onMounted, PropType } from 'vue';

const props = defineProps({
    handler: {
        type: Object as PropType<DataTableHandler>,
        required: true
    },
    selection:{
        type: Array as PropType<any[]>,
    },
    emptyMessage: {
        type: String,
        default: 'No data available.'
    },
});
const selection = computed({
    get: () => props.selection,
    set: (value) => emit('update:selection', value)
});

onMounted(props.handler.loadData);  

const emit = defineEmits(['update:selection', 'update:filters']);
</script>