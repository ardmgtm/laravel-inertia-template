<template>
    <aside class="hidden lg:flex flex-col flex-none w-72 border border-surface-300 bg-surface-50 mt-[72px] m-2 rounded-lg p-2">
        <div class="flex-none border-surface-300 pb-1">
            <slot name="header"></slot>
        </div>
        <nav class="flex-1 overflow-y-scroll no-scrollbar">
            <ul>
                <li v-for="menuItem in sideMenuItemData" >
                    <SidemenuItem
                        :label="menuItem.label" 
                        :sparator="menuItem.separator"
                        :icon="menuItem.icon" 
                        :url="menuItem.url" 
                        :items="menuItem.items" />
                </li>
            </ul>

        </nav>
        <div class="flex-none border-t-2 border-surface-300 pt-1">
            <ul>
                <li>
                    <SidemenuItem icon="pi pi-info-circle" label="Information" />
                </li>
            </ul>
        </div>
    </aside>
</template>
<script setup lang="ts">
import { sideMenuItemData } from "@/Core/Configs/sidemenu-item";
import SidemenuItem from "./SidemenuItem.vue";
</script>