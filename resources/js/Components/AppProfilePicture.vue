<template>
    <img 
        :src="url ?? user?.profile_picture" 
        alt="profile" 
        :style="{ width: size + 'px', height: size + 'px', minWidth: size + 'px', minHeight: size + 'px' }"
        class="rounded-full"
        v-if="url || user?.profile_picture"/>
    <div
        v-else
        class="rounded-full flex items-center justify-center font-bold select-none" 
        :style="{ 
            backgroundColor: backgroundColor,
            color: textColor,
            width: size + 'px',
            height: size + 'px',
            fontSize: size * 0.4 + 'px'
        }"
    >
        {{ firstLetter }}
    </div>
</template>

<script setup lang="ts">
import { User } from '@/Core/Models/user';
import { computed } from 'vue';

interface Props {
    name?: string;
    url?: string;
    size?: number;
    user?: User;
}

const props = withDefaults(defineProps<Props>(), {
    size: 40
});

const firstLetter = computed(() => {
    return props.name?.charAt(0).toUpperCase() ?? props.user?.name.charAt(0).toUpperCase() ?? '?';
});

const backgroundColor = computed(() => {
    let name = props.name ?? props.user?.name ?? '?';
    const hash = name.split('').reduce((acc, char) => {
        return char.charCodeAt(0) + ((acc << 5) - acc);
    }, 0);
    
    const h = hash % 360;
    const s = 60;
    const l = 85;
    
    return `hsl(${h}, ${s}%, ${l}%)`;
});

const textColor = computed(() => {
    return '#2D3748';
});
</script>