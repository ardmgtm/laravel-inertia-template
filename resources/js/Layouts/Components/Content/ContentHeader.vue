<template>
    <div class="flex items-start flex-col flex-1">
        <Breadcrumb :home="home" :model="breadcrumbs" class="bg-surface-50 p-0 mb-4" v-if="showBreadcrumbs"/>
        <div class="flex items-end justify-between w-full">
            <div class="font-medium text-3xl text-surface-900 dark:text-surface-0">
                {{ props.title }}
            </div>
            <div class="flex-none">
                <slot name="action"/>
            </div>
        </div>
    </div>
    <hr class="my-4"/>
</template>
<script setup lang="ts">
import { computed, ref } from 'vue';
import { MenuItem } from 'primevue/menuitem';

const props = defineProps({
    title:{
        type:String,
        required:false,
    },
    breadcrumbs:{
        type: Array as () => MenuItem[],
        required:false,
    }
})

const home = ref({
    icon: 'pi pi-home'
});
const showBreadcrumbs = computed(()=>props.breadcrumbs!=null && props.breadcrumbs.length > 0);
</script>