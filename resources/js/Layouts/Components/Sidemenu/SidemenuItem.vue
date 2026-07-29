<template>
    <div v-if="props.separator" class="my-2">
        <span v-if="!props.collapsed" class="p-2 font-bold text-gray-500">{{ props.label }}</span>
        <div v-else class="border-t border-surface-300 mx-2"></div>
    </div>
    <div v-else class="relative">
        <component :is="url ? Link : 'div'" v-ripple :href="props.url ?? ''" ref="menuItemRef"
            :class="menuItemClass" @click.stop="onclickHandle" v-tooltip.right="tooltipText">
            <component :is="(props.counter != null && props.collapsed) ? OverlayBadge : 'div'" severity="danger"
                class="flex items-center">

                <div v-if="props.icon != null" :class="iconBoxClass">
                    <i class="group-hover:text-primary transition-colors" :class="iconClass" />
                </div>
                <div v-else>
                    <div class="h-8 w-8 flex items-center justify-center">
                        <div :class="dotClass"></div>
                    </div>
                </div>
            </component>
            <span v-if="!props.collapsed" class="flex-1 text-ellipsis overflow-hidden whitespace-nowrap">
                {{ props.label }}
            </span>
            <div class="flex-none" v-if="props.items != null && !props.collapsed">
                <i class="pi pi-chevron-down transition duration-300 text-gray-400"
                    :class="{ '-rotate-180': submenuExpand }"></i>
            </div>
            <div v-else-if="props.counter != null && !props.collapsed" class="flex-none">
                <Badge :value="props.counter" severity="danger" class="ml-2" />
            </div>
        </component>

        <!-- Popup Menu for Collapsed State (using Teleport to body) -->
        <Teleport to="body">
            <Transition name="popup">
                <div v-if="showPopup && props.collapsed && props.items" ref="popupRef"
                    class="fixed bg-white rounded-lg shadow-xl border border-gray-200 py-2 min-w-[200px] max-w-[280px] max-h-[80vh] overflow-y-auto custom-scrollbar"
                    style="z-index: 9999;" :style="popupPosition" @click.stop>
                    <div class="px-4 py-2 border-b border-gray-200 mb-2">
                        <span class="font-semibold text-primary">{{ props.label }}</span>
                    </div>
                    <div class="py-1">
                        <SidemenuPopupItem v-for="subMenu in props.items" :item="subMenu" @close-popup="closePopup" />
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Regular Submenu for Expanded State -->
        <ul v-if="!props.collapsed" class="ml-4 transition-height duration-300 ease-in-out overflow-hidden"
            :style="{ height: submenuExpand ? submenuHeight : '0' }">
            <li v-for="subMenu in props.items">
                <SidemenuItem :label="subMenu.label" :url="subMenu.url" @item-active="updateActiveState"
                    v-if="can(subMenu.permissions as string | string[])" :items="subMenu.items"
                    :collapsed="props.collapsed" @click="onclickHandle" />
            </li>
        </ul>
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import { MenuItem } from "primevue/menuitem";
import { Link, usePage } from "@inertiajs/vue3";
import { can } from "@/Core/Utils/permission-check";
import { SideMenuItem } from "@/Core/Configs/sidemenu-item";
import SidemenuPopupItem from "./SidemenuPopupItem.vue";
import { OverlayBadge } from "primevue";

const emit = defineEmits<{
    "item-active": []
}>();

const props = withDefaults(defineProps<{
    label?: string | Function;
    separator?: boolean;
    counter?: number;
    icon?: string;
    url?: string;
    items?: SideMenuItem[];
    collapsed?: boolean;
}>(), {
    collapsed: false
});

const submenuExpand = ref(false);
const isActive = ref(false);
const showPopup = ref(false);
const menuItemRef = ref<HTMLElement | null>(null);
const popupRef = ref<HTMLElement | null>(null);
const popupPosition = ref({});
const currentUrl = computed(() => usePage().url);

const hasActiveChild = computed(() => {
    if (!props.items) return false;

    const checkActive = (items: SideMenuItem[]): boolean => {
        return items.some(item => {
            if (item.url && currentUrl.value.startsWith(item.url)) return true;
            return item.items ? checkActive(item.items) : false;
        });
    };

    return checkActive(props.items);
});

const isHighlighted = computed(() => 
    isActive.value || submenuExpand.value || showPopup.value || hasActiveChild.value
);

const menuItemClass = computed(() => [
    "flex gap-2 p-2 items-center rounded-lg cursor-pointer group ripple-box hover:bg-surface-100 transition-all duration-300",
    { "text-primary font-bold": isHighlighted.value },
    { "justify-center": props.collapsed }
]);

const iconBoxClass = computed(() => [
    "rounded-lg border h-8 w-8 flex flex-none items-center justify-center group-hover:border-primary transition-colors",
    isHighlighted.value ? "border-primary" : "border-surface-300"
]);

const iconClass = computed(() => [
    props.icon,
    isHighlighted.value ? "text-primary" : "text-surface-500"
]);

const dotClass = computed(() => [
    "rounded-full h-2 w-2 group-hover:bg-primary transition-colors",
    isHighlighted.value ? "bg-primary" : "bg-surface-500"
]);

const tooltipText = computed(() => 
    props.collapsed && !props.items ? props.label : ''
);

function updateActiveState() {
    isActive.value = true
    if (props.items) {
        submenuExpand.value = true;
    }
};

// Watch popup state to manage event listeners
watch(showPopup, (newValue) => {
    if (newValue) {
        document.addEventListener('click', handleClickOutside);
        window.addEventListener('scroll', handleScroll, true);
        window.addEventListener('resize', handleResize);
    } else {
        document.removeEventListener('click', handleClickOutside);
        window.removeEventListener('scroll', handleScroll, true);
        window.removeEventListener('resize', handleResize);
    }
});

onMounted(() => {
    isActive.value = props.url ? currentUrl.value.startsWith(props.url) : false;
    if (isActive.value) {
        emit('item-active');
    }

    // Auto-expand if has active child
    if (hasActiveChild.value && props.items) {
        submenuExpand.value = true;
    }
});

onUnmounted(() => {
    // Cleanup if popup is still showing
    if (showPopup.value) {
        document.removeEventListener('click', handleClickOutside);
        window.removeEventListener('scroll', handleScroll, true);
        window.removeEventListener('resize', handleResize);
    }
});

function handleClickOutside(event: MouseEvent) {
    if (showPopup.value && popupRef.value && !popupRef.value.contains(event.target as Node)
        && menuItemRef.value && !menuItemRef.value.contains(event.target as Node)) {
        closePopup();
    }
}

function closePopup() {
    showPopup.value = false;
}

function handleScroll() {
    if (showPopup.value) {
        calculatePopupPosition();
    }
}

function handleResize() {
    if (showPopup.value) {
        calculatePopupPosition();
    }
}

const submenuHeight = computed(() => {
    if (!props.items) return '0px';
    
    const baseHeight = 48;
    const calculateHeight = (items: MenuItem[]): number => {
        return items.reduce((total, item) => {
            const itemHeight = 1;
            const childHeight = item.items ? calculateHeight(item.items) / baseHeight : 0;
            return total + itemHeight + childHeight;
        }, 0);
    };

    const totalHeight = calculateHeight(props.items) * baseHeight;
    return `${totalHeight}px`;
});

async function onclickHandle() {
    if (props.items) {
        if (props.collapsed) {
            // Show popup for collapsed sidebar
            showPopup.value = !showPopup.value;
            if (showPopup.value) {
                await nextTick();
                calculatePopupPosition();
            }
        } else {
            // Toggle submenu for expanded sidebar
            submenuExpand.value = !submenuExpand.value;
        }
    }
}

const countVisibleItems = (items: SideMenuItem[]): number => {
    return items.reduce((count, item) => {
        if (!can(item.permissions as string | string[])) return count;
        return count + 1 + (item.items ? countVisibleItems(item.items) : 0);
    }, 0);
};

function calculatePopupPosition() {
    if (!menuItemRef.value || !props.items) return;
    
    const rect = menuItemRef.value.getBoundingClientRect();
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;
    const popupWidth = 280;
    const itemHeight = 40;
    const headerPadding = 80;

    const totalItems = countVisibleItems(props.items);
    const popupEstimatedHeight = totalItems * itemHeight + headerPadding;

    let top = rect.top;
    let left = rect.right + 8;

    // Adjust vertical position if overflow
    if (top + popupEstimatedHeight > viewportHeight) {
        top = Math.max(10, viewportHeight - popupEstimatedHeight - 10);
    }

    // Adjust horizontal position if overflow
    if (left + popupWidth > viewportWidth) {
        left = rect.left - popupWidth - 8;
    }

    popupPosition.value = {
        top: `${top}px`,
        left: `${left}px`,
    };
}
</script>

<style scoped>
.popup-enter-active,
.popup-leave-active {
    transition: all 0.2s ease;
}

.popup-enter-from {
    opacity: 0;
    transform: translateX(-10px);
}

.popup-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>