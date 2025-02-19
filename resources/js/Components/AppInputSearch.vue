<template>
    <IconField>
        <InputIcon>
            <i class="pi pi-search" />
        </InputIcon>
        <InputText placeholder="Search" v-model="model" fluid v-bind="$attrs" v-on="$attrs"/>
    </IconField>
</template>
<script setup lang="ts">
import { defineProps, defineEmits, ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

const model = ref(props.modelValue);

watch(model, (newValue) => {
    emit('update:modelValue', newValue);
});
</script>