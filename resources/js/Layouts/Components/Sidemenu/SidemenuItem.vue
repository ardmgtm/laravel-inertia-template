<template>
    <div v-if="props.sparator" class="my-2">
        <span class="p-2 font-bold text-gray-500">{{ props.label }}</span>
    </div>
    <div v-else>
        <div v-ripple
            class="flex gap-2 p-2 items-center rounded-lg cursor-pointer group ripple-box bg-surface-50 hover:bg-surface-100"
            :class="{ 'text-primary font-bold': isActive || submenuExpand }" @click.stop="onclickHandle">
            <div v-if="props.icon != null"
                class="rounded-lg border h-8 w-8 flex items-center justify-center group-hover:border-primary"
                :class="{ 'border-primary': isActive, 'border-surface-300': !isActive }">
                <i class="group-hover:text-primary"
                    :class="[props.icon, { 'text-primary': isActive || submenuExpand, 'text-surface-500': !isActive && !submenuExpand }]" />
            </div>
            <div v-else>
                <div class="h-8 w-8 flex items-center justify-center">
                    <div class="rounded-full h-2 w-2 group-hover:bg-primary"
                        :class="{ 'bg-primary': isActive || submenuExpand, 'bg-surface-500': !isActive && !submenuExpand }">
                    </div>
                </div>
            </div>
            <span class="flex-1">{{ props.label }}</span>
            <div class="flex-none" v-if="props.items != null">
                <i class="pi pi-chevron-down transition duration-300 text-gray-400"
                    :class="{ '-rotate-180': submenuExpand, 'rotate-0': !submenuExpand }"></i>
            </div>
        </div>
        <ul class="ml-4 transition-height duration-300 ease-in-out overflow-hidden"
            :style="{ height: submenuExpand ? submenuHeight : '0' }">
            <li v-for="subMenu in props.items">
                <SidemenuItem :label="subMenu.label" :url="subMenu.url" @item-active="updateActiveState"
                    v-if="can(subMenu.permissions as string | string[])" :items="subMenu.items"
                    @click="onclickHandle" />
            </li>
        </ul>
    </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { MenuItem } from "primevue/menuitem";
import { router, usePage } from "@inertiajs/vue3";
import { can } from "@/Core/Utiils/permission-check";
import { SideMenuItem } from "@/Core/Configs/sidemenu-item";

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
    }
})

const submenuExpand = ref(false);
const isActive = ref(false);

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
});

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

function onclickHandle() {
    if (props.items) {
        submenuExpand.value = !submenuExpand.value;
    } else if (props.url) {
        router.visit(props.url);
    }
}
</script>