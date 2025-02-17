import { DataTableFilterEvent, DataTableFilterMetaData, DataTablePageEvent, DataTableSortEvent } from "primevue";
import axios from 'axios';
import { ref, Ref } from "vue";

interface DataTableRequest {
    page: number;
    size: number;
    sorts?: Array<any>;
    filters?: Array<any>;
}

interface DataTableResponse {
    data: Array<any>;
    totalRecords: number;
}

class DataTableHandler {
    private readonly url: string;
    loading: Ref<boolean>;
    page: number;
    size: number;
    filters: { [key: string]: DataTableFilterMetaData };
    loadedData: Ref<DataTableResponse>;
    private readonly request: DataTableRequest;

    constructor(url: string) {
        this.url = url;
        this.page = 1;
        this.size = 10;
        this.loading = ref(false);
        this.loadedData = ref({ data: [], totalRecords: 0 });
        this.filters = {};
        this.request = {
            page: 1,
            size: 10,
            sorts: [],
            filters: []
        };
        this.loadData();
    }

    loadData = (): void => {
        this.loading.value = true;
        axios.get(this.url, {
            params: this.request
        }).then((response) => {
            this.loadedData.value = {
                data: response.data.data,
                totalRecords: response.data.totalRecords
            };
        }).catch(error => {
            console.error('Error loading data', error);
            throw error;
        }).finally(() => {
            this.loading.value = false;
        });
    }

    onSort = (event: DataTableSortEvent): void => {
        this.loading.value = true;
        const field = event.sortField;
        let sortOrder = null;

        if (event.sortOrder === 1) {
            sortOrder = 'asc';
        } else if (event.sortOrder === -1) {
            sortOrder = 'desc';
        }

        this.request.sorts = [field, sortOrder];
        this.loadData();
    }

    onFilter = (event: DataTableFilterEvent): void => {
        let filters = event.filters as { [key: string]: DataTableFilterMetaData };
        this.request.filters = Object.entries(filters)
            .filter(([_, filterMeta]) => filterMeta.value !== null && filterMeta.value !== undefined)
            .map(([field, filterMeta]) => [
                field,
                filterMeta.matchMode,
                filterMeta.value
            ]);

        this.request.page = 1;
        this.loadData();
    }

    onPage = (event: DataTablePageEvent): void => {
        this.page = event.page + 1;
        this.size = event.rows;
        this.request.page = event.page + 1;
        this.request.size = event.rows;
        this.loadData();
    }
}

export { DataTableHandler };