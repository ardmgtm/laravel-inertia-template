<template>
    <div class="card p-6 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 shadow-sm hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Recent Transactions</h2>
                <p class="text-sm text-gray-500 mt-1">Latest product orders</p>
            </div>
            <Button variant="text" icon="pi pi-ellipsis-v" severity="secondary" rounded v-tooltip.bottom="'Action'" class="hover:bg-gray-100" />
        </div>
        <Divider class="mb-4" />
        <DataTable :value="transactions" class="min-w-full" :rows="5" stripedRows>
            <Column field="id" header="ID Transaksi" class="text-center font-semibold">
                <template #body="slotProps">
                    <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-sm font-medium">{{ slotProps.data.id }}</span>
                </template>
            </Column>
            <Column field="product" header="Nama Produk">
                <template #body="slotProps">
                    <span class="text-gray-700 font-medium">{{ slotProps.data.product }}</span>
                </template>
            </Column>
            <Column field="quantity" header="Jumlah" class="text-right">
                <template #body="slotProps">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-sm font-semibold">{{ slotProps.data.quantity }}</span>
                </template>
            </Column>
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
