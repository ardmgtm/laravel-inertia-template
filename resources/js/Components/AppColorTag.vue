<template>
    <div :style="{
        backgroundColor: backgroundColor,
        color: textColor,
    }" class="rounded-md flex items-center justify-center font-bold select-none px-2 py-1 text-sm">
        {{ props.label }}
    </div>
</template>
<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    label: string;
    size?: number;
}

const props = withDefaults(defineProps<Props>(), {
    size: 40
});

const backgroundColor = computed(() => {
    // Generate a pastel color based on the name
    const hash = props.label.split('').reduce((acc, char) => {
        return char.charCodeAt(0) + ((acc << 5) - acc);
    }, 0);

    // Generate HSL color with high lightness for pastel effect
    const h = hash % 360;
    const s = 60; // Lower saturation for pastel
    const l = 85; // Higher lightness for pastel

    return `hsl(${h}, ${s}%, ${l}%)`;
});

const textColor = computed(() => {
    // Extract HSL values from the background color
    const hsl = backgroundColor.value.match(/\d+/g);
    if (!hsl) return '#000'; // Fallback to black if parsing fails

    const h = parseInt(hsl[0]);
    const s = parseInt(hsl[1]);
    const l = parseInt(hsl[2]) - 50; // Decrease lightness more for darker text

    return `hsl(${h}, ${s}%, ${l}%)`;
});
</script>