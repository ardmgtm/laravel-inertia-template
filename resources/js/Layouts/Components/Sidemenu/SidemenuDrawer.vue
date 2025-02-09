<template>
    <Drawer 
        ref="drawer" 
        :show-close-icon="false" 
        position="left" 
        class="p-0 w-80 h-full overflow-hidden bg-surface-50"
        v-model:visible="visible" @hide="updateVisible(false)">
        <template #header>
            <div class="flex-none border-surface-300 pb-1 flex items-center justify-center w-full">
                <img src="/vite.svg" class="h-14" alt="App Icon"/>
            </div>
        </template>
        <aside class="flex flex-col">
            <nav class="flex-1 overflow-y-scroll no-scrollbar" aria-label="Main Side Menu Navigation">
                <ul>
                    <li v-for="menuItem in sideMenuItemData">
                        <SidemenuItem 
                            :label="menuItem.label"
                            :sparator="menuItem.separator" 
                            :icon="menuItem.icon" 
                            :url="menuItem.url"
                            :items="menuItem.items" 
                            @redirect-page="closeDrawer"/>
                    </li>
                </ul>
            </nav>
        </aside>
        <template #footer>
            <nav class="flex-none border-t-2 border-surface-300 pt-1" aria-label="Footer Side Menu Navigation">
                <ul>
                    <SidemenuItem icon="pi pi-user" label="Account" />
                    <SidemenuItem icon="pi pi-cog" label="Settings" />
                </ul>
            </nav>
        </template>
    </Drawer>
</template>
<script setup>
import { ref, watch } from 'vue';
import SidemenuItem from './SidemenuItem.vue';
import { sideMenuItemData } from '@/Core/Configs/sidemenu-item';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});
const emit = defineEmits([
    'update:visible'
]);


const drawer = ref();
const visible = ref(props.visible);

watch(() => props.visible, (newValue) => {
    visible.value = newValue;
});

function updateVisible(value) {
    visible.value = value;
    emit('update:visible', value);
};

function closeDrawer() {
    updateVisible(false);
}
</script>