<template>
    <div v-if="props.sparator" class="my-2">
        <span v-if="!props.collapsed" class="p-2 font-bold text-gray-500">{{ props.label }}</span>
        <div v-else class="border-t border-surface-300 mx-2"></div>
    </div>
    <div v-else class="relative">
        <component 
            :is="url ? Link : 'div'" 
            v-ripple 
            :href="props.url ?? ''"
            ref="menuItemRef"
            class="flex gap-2 p-2 items-center rounded-lg cursor-pointer group ripple-box hover:bg-surface-100 transition-all duration-300"
            :class="[
                { 'text-primary font-bold': isActive || submenuExpand || showPopup || hasActiveChild },
                props.collapsed ? 'justify-center' : ''
            ]" 
            @click.stop="onclickHandle"
            v-tooltip.right="props.collapsed && !props.items ? props.label : ''">
            <div v-if="props.icon != null"
                class="rounded-lg border h-8 w-8 flex flex-none items-center justify-center group-hover:border-primary transition-colors"
                :class="{ 'border-primary': isActive || submenuExpand || showPopup || hasActiveChild, 'border-surface-300': !isActive && !submenuExpand && !showPopup && !hasActiveChild }">
                <i class="group-hover:text-primary transition-colors"
                    :class="[props.icon, { 'text-primary': isActive || submenuExpand || showPopup || hasActiveChild, 'text-surface-500': !isActive && !submenuExpand && !showPopup && !hasActiveChild }]" />
            </div>
            <div v-else>
                <div class="h-8 w-8 flex items-center justify-center">
                    <div class="rounded-full h-2 w-2 group-hover:bg-primary transition-colors"
                        :class="{ 'bg-primary': isActive || submenuExpand || showPopup || hasActiveChild, 'bg-surface-500': !isActive && !submenuExpand && !showPopup && !hasActiveChild }">
                    </div>
                </div>
            </div>
            <span v-if="!props.collapsed" class="flex-1 text-ellipsis overflow-hidden whitespace-nowrap">{{ props.label }}</span>
            <div class="flex-none" v-if="props.items != null && !props.collapsed">
                <i class="pi pi-chevron-down transition duration-300 text-gray-400"
                    :class="{ '-rotate-180': submenuExpand, 'rotate-0': !submenuExpand }"></i>
            </div>
        </component>

        <!-- Popup Menu for Collapsed State (using Teleport to body) -->
        <Teleport to="body">
            <Transition name="popup">
                <div 
                    v-if="showPopup && props.collapsed && props.items"
                    ref="popupRef"
                    class="fixed bg-white rounded-lg shadow-xl border border-gray-200 py-2 min-w-[200px] max-w-[280px] max-h-[80vh] overflow-y-auto custom-scrollbar"
                    style="z-index: 9999;"
                    :style="popupPosition"
                    @click.stop>
                    <div class="px-4 py-2 border-b border-gray-200 mb-2">
                        <span class="font-semibold text-primary">{{ props.label }}</span>
                    </div>
                    <div class="py-1">
                        <SidemenuPopupItem 
                            v-for="subMenu in props.items" 
                            :item="subMenu"
                            @close-popup="closePopup"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Regular Submenu for Expanded State -->
        <ul v-if="!props.collapsed" class="ml-4 transition-height duration-300 ease-in-out overflow-hidden"
            :style="{ height: submenuExpand ? submenuHeight : '0' }">
            <li v-for="subMenu in props.items">
                <SidemenuItem 
                    :label="subMenu.label" 
                    :url="subMenu.url" 
                    @item-active="updateActiveState"
                    v-if="can(subMenu.permissions as string | string[])" 
                    :items="subMenu.items"
                    :collapsed="props.collapsed"
                    @click="onclickHandle" 
                />
            </li>
        </ul>
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import { MenuItem } from "primevue/menuitem";
import { Link, usePage } from "@inertiajs/vue3";
import { can } from "@/Core/Utils/permission-check";
import { SideMenuItem } from "@/Core/Configs/sidemenu-item";
import SidemenuPopupItem from "./SidemenuPopupItem.vue";

const emit = defineEmits(["item-active"]);

const props = defineProps({
    label: {
        type: [String, Function],
        required: false
    },
    sparator: {
        type: Boolean,
        required: false
    },
    icon: {
        type: String,
        required: false
    },
    url: {
        type: String,
        required: false,
    },
    items: {
        type: Array as () => SideMenuItem[],
        required: false,
    },
    collapsed: {
        type: Boolean,
        default: false
    }
})

const submenuExpand = ref(false);
const isActive = ref(false);
const showPopup = ref(false);
const menuItemRef = ref<HTMLElement | null>(null);
const popupRef = ref<HTMLElement | null>(null);
const popupPosition = ref({});

const hasActiveChild = computed(() => {
    if (!props.items) return false;
    
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
    
    return checkActive(props.items);
});

function updateActiveState() {
    isActive.value = true
    if (props.items) {
        submenuExpand.value = true;
    }
};

onMounted(() => {
    isActive.value = props.url ? usePage().url.startsWith(props.url) : false;
    if (isActive.value) {
        emit('item-active');
    }
    
    // Auto-expand if has active child
    if (hasActiveChild.value && props.items) {
        submenuExpand.value = true;
    }
    
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('scroll', handleScroll, true);
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('scroll', handleScroll, true);
    window.removeEventListener('resize', handleResize);
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

function calculatedHeight(items: MenuItem[]): number {
    const baseHeight = 48;
    const subItemCount = items.reduce((total, item) => {
        if (item.items) {
            return total + 1 + calculatedHeight(item.items) / baseHeight;
        }
        return total + 1;
    }, 0);

    return parseInt((subItemCount * baseHeight).toString());
};

const submenuHeight = computed(() => {
    return props.items ? calculatedHeight(props.items) + 'px' : '0px';
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

function calculatePopupPosition() {
    if (menuItemRef.value) {
        const rect = menuItemRef.value.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const popupWidth = 280; // max-w-[280px]
        
        // Calculate estimated height based on nested items
        const countAllItems = (items: SideMenuItem[]): number => {
            return items.reduce((count, item) => {
                if (can(item.permissions as string | string[])) {
                    count += 1;
                    if (item.items) {
                        count += countAllItems(item.items);
                    }
                }
                return count;
            }, 0);
        };
        
        const totalItems = props.items ? countAllItems(props.items) : 0;
        const popupEstimatedHeight = totalItems * 40 + 80; // 40px per item + header + padding
        
        let top = rect.top;
        let left = rect.right + 8; // 8px gap from sidebar
        
        // Adjust if popup would overflow bottom of viewport
        if (top + popupEstimatedHeight > viewportHeight) {
            top = Math.max(10, viewportHeight - popupEstimatedHeight - 10);
        }
        
        // Adjust if popup would overflow right of viewport
        if (left + popupWidth > window.innerWidth) {
            left = rect.left - popupWidth - 8; // Show on left side instead
        }
        
        popupPosition.value = {
            top: `${top}px`,
            left: `${left}px`,
        };
    }
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