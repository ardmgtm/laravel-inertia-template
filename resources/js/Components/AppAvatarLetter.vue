<template>
    <div 
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
import { computed } from 'vue';

interface Props {
    name: string;
    size?: number;
}

const props = withDefaults(defineProps<Props>(), {
    size: 40
});

const firstLetter = computed(() => {
    return props.name.charAt(0).toUpperCase();
});

const backgroundColor = computed(() => {
    // Generate a pastel color based on the name
    const hash = props.name.split('').reduce((acc, char) => {
        return char.charCodeAt(0) + ((acc << 5) - acc);
    }, 0);
    
    // Generate HSL color with high lightness for pastel effect
    const h = hash % 360;
    const s = 60; // Lower saturation for pastel
    const l = 85; // Higher lightness for pastel
    
    return `hsl(${h}, ${s}%, ${l}%)`;
});

const textColor = computed(() => {
    // Always return a dark color for contrast
    return '#2D3748'; // Tailwind gray-800
});
</script>