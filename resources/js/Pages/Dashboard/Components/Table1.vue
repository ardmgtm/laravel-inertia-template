<template>
    <div class="card p-4 rounded-xl border border-surface-300">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold">Table Example</h2>
            <Button variant="text" icon="pi pi-ellipsis-v" severity="secondary" rounded v-tooltip.bottom="'Action'" />
        </div>
        <Divider />
        <DataTable :value="transactions" class="min-w-full border border-gray-300 rounded" :rows="5">
            <Column field="id" header="ID Transaksi" class="text-center" />
            <Column field="product" header="Nama Produk" />
            <Column field="quantity" header="Jumlah" class="text-right" />
        </DataTable>
    </div>
</template>

<script>
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

export default {
    components: {
        DataTable,
        Column,
    },
    setup() {
        const transactions = ref([
            { id: 'TRX001', product: 'Produk A', quantity: 10 },
            { id: 'TRX002', product: 'Produk B', quantity: 5 },
            { id: 'TRX003', product: 'Produk C', quantity: 8 },
            { id: 'TRX004', product: 'Produk D', quantity: 2 },
            { id: 'TRX005', product: 'Produk E', quantity: 15 },
        ]);

        return { transactions };
    },
};
</script>

<style scoped>
/* Tailwind CSS sudah digunakan via class, tambahan styling jika perlu */
</style>
