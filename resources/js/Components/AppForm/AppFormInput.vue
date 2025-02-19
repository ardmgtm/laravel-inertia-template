<template>
    <InputText :id="id" v-model="modelValue" @input="handleInput" v-bind="$attrs" :autocomplete="autocomplete" :invalid="isInvalid" fluid/>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue';

interface Props {
    id?: string;
    modelValue: any;
    autocomplete?: string;
}

const props = withDefaults(defineProps<Props>(), {
    autocomplete: 'off'
});

const emit = defineEmits(['update:modelValue', 'input']);
const formErrors = inject('formErrors') as any;
const clearFormError = inject('clearFormError') as any;

const isInvalid = computed(() => {
    return formErrors && formErrors.hasOwnProperty(props.id);
});

const modelValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const handleInput = (event: Event) => {
    clearFormError(props.id);
    emit('input', event);
};

</script>