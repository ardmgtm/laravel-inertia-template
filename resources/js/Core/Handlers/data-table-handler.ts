import { DataTableFilterEvent, DataTableFilterMetaData, DataTablePageEvent, DataTableSortEvent } from "primevue";
import axios from 'axios';
import { ref, Ref } from "vue";

interface DataTableRequest {
    page: number;
    size: number;
    sorts?: any[];
    filters?: any[];
}

interface DataTableResponse {
    data: any[];
    totalRecords: number;
}

interface DataTableHandler {
    loading: Ref<boolean>;
    page: number;
    size: number;
    filters: { [key: string]: DataTableFilterMetaData };
    loadedData: Ref<DataTableResponse>;
    loadData: () => void;
    onSort: (event: DataTableSortEvent) => void;
    onFilter: (event: DataTableFilterEvent) => void;
    onPage: (event: DataTablePageEvent) => void;
    url: string;        // Missing property
    request: DataTableRequest;   // Missing property
}
function createDataTableHandler(url: string, request: unknown = {}): DataTableHandler {
    const handler = {
        loading: ref(false),
        page: 1,
        size: 10,
        filters: {},
        loadedData: ref({ data: [], totalRecords: 0 }),
        url,
        request: { page: 1, size: 10, filters:[], sorts:[] } as DataTableRequest,
        
        loadData: () => {
            handler.loading.value = true;
            axios.get(handler.url, {
                params: handler.request
            }).then((response) => {
                handler.loadedData.value = {
                    data: response.data.data,
                    totalRecords: response.data.totalRecords
                };
            }).catch(error => {
                throw error;
            }).finally(() => {
                handler.loading.value = false;
            });
        },

        onSort: (event: DataTableSortEvent) => {
            handler.loading.value = true;
            const field = event.sortField;
            let sortOrder = null;

            if (event.sortOrder === 1) {
                sortOrder = 'asc';
            } else if (event.sortOrder === -1) {
                sortOrder = 'desc';
            }

            handler.request.sorts = [field, sortOrder];
            handler.loadData();
        },

        onFilter: (event: DataTableFilterEvent) => {
            let filters = event.filters as { [key: string]: DataTableFilterMetaData };
            handler.request.filters = Object.entries(filters)
                .filter(([_, filterMeta]) => filterMeta.value !== null && filterMeta.value !== undefined)
                .map(([field, filterMeta]) => [
                    field,
                    filterMeta.matchMode,
                    filterMeta.value
                ]);

            handler.request.page = 1;
            handler.loadData();
        },

        onPage: (event: DataTablePageEvent) => {
            handler.page = event.page + 1;
            handler.size = event.rows;
            handler.request.page = event.page + 1;
            handler.request.size = event.rows;
            handler.loadData();
        }
    };

    return handler;
}

export type { DataTableHandler };
export { createDataTableHandler };