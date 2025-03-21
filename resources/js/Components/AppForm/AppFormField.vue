<template>
    <FormField v-bind="$attrs" v-on="$attrs" :name="name" v-slot="$field" :class="[
        'flex',
        effectiveLabelPosition === 'top' ? 'flex-col' : 'flex-col md:flex-row'
    ]">
        <label :for="name" :style="{
            marginBottom: effectiveLabelPosition === 'left' ? '0' : '0.5rem',
            width: effectiveLabelPosition === 'left' ? `${effectiveLabelWidth}px` : '100%'
        }" :class="effectiveLabelPosition === 'left' ? 'flex-none' : ''">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div :class="[effectiveLabelPosition === 'left' ? 'flex-1' : 'w-full']">
            <slot/>
            <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                {{ $field.error?.message || formErrorsMessage }}
            </Message>
        </div>
    </FormField>
</template>

<script setup lang="ts">
import { computed, inject, onMounted } from 'vue';

interface Props {
    name?: string;
    label?: string;
    required?: boolean;
    labelWidth?: number;
    labelPosition?: 'top' | 'left';
}

const props = withDefaults(defineProps<Props>(), {
    required: false
});

const formConfig = inject('formConfig') as any;

const effectiveLabelPosition = computed(() =>
    props.labelPosition || formConfig.value.labelPosition
);

const effectiveLabelWidth = computed(() =>
    props.labelWidth || formConfig.value.labelWidth
);

const registerFormField = inject('registerFormField') as (field: any) => void;
const formErrors = inject('formErrors') as any;

const formErrorsMessage = computed(() => {
    const error = formErrors?.value?.[props?.name ?? ''] ?? '';
    return Array.isArray(error) ? error[0] : error;
});

onMounted(() => {
    registerFormField({
        name: props.name,
        label: props.label,
        required: props.required,
    });
});
</script>