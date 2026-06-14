<template>
    <div v-if="can(props.item.permissions as string | string[])">
        <!-- Item without children -->
        <Link 
            v-if="!props.item.items"
            :href="props.item.url ?? ''"
            class="flex items-center gap-3 px-4 py-2 hover:bg-surface-100 transition-colors cursor-pointer rounded-md mx-1"
            :class="{ 'text-primary font-semibold bg-primary/5': isActive }"
            @click="emit('close-popup')">
            <i v-if="props.item.icon" :class="props.item.icon" class="text-sm"></i>
            <div v-else class="w-2 h-2 rounded-full bg-gray-400"></div>
            <span class="flex-1">{{ props.item.label }}</span>
        </Link>

        <!-- Item with children (nested) -->
        <div v-else>
            <div
                class="flex items-center gap-3 px-4 py-2 hover:bg-surface-100 transition-colors cursor-pointer rounded-md mx-1"
                :class="{ 'text-primary font-semibold': hasActiveChild || expanded }"
                @click="toggleExpand">
                <i v-if="props.item.icon" :class="props.item.icon" class="text-sm"></i>
                <div v-else class="w-2 h-2 rounded-full bg-gray-400"></div>
                <span class="flex-1">{{ props.item.label }}</span>
                <i class="pi pi-chevron-down text-xs transition-transform duration-200"
                    :class="{ 'rotate-180': expanded }"></i>
            </div>

            <!-- Nested children -->
            <div 
                class="ml-4 overflow-hidden transition-all duration-300 ease-in-out"
                :style="{ maxHeight: expanded ? maxHeight : '0px' }">
                <SidemenuPopupItem 
                    v-for="child in props.item.items"
                    :item="child"
                    @close-popup="emit('close-popup')"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { can } from "@/Core/Utils/permission-check";
import { SideMenuItem } from "@/Core/Configs/sidemenu-item";

const emit = defineEmits(["close-popup"]);

const props = defineProps({
    item: {
        type: Object as () => SideMenuItem,
        required: true
    }
});

const expanded = ref(false);

const isActive = computed(() => {
    return props.item.url ? usePage().url.startsWith(props.item.url) : false;
});

const hasActiveChild = computed(() => {
    if (!props.item.items) return false;
    
    const checkActive = (items: SideMenuItem[]): boolean => {
        return items.some(item => {
            if (item.url && usePage().url.startsWith(item.url)) {
                return true;
            }
            if (item.items) {
                return checkActive(item.items);
            }
            return false;
        });
    };
    
    return checkActive(props.item.items);
});

const maxHeight = computed(() => {
    if (!props.item.items) return '0px';
    
    const countItems = (items: SideMenuItem[]): number => {
        return items.reduce((count, item) => {
            if (can(item.permissions as string | string[])) {
                count += 1;
                if (item.items) {
                    count += countItems(item.items);
                }
            }
            return count;
        }, 0);
    };
    
    const itemCount = countItems(props.item.items);
    return `${itemCount * 40}px`; // 40px per item
});

function toggleExpand() {
    expanded.value = !expanded.value;
}

onMounted(() => {
    // Auto-expand if has active child
    if (hasActiveChild.value) {
        expanded.value = true;
    }
});
</script>

<style scoped>
/* Additional styles if needed */
</style>
