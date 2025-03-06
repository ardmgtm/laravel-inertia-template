<template>
    <Form 
        class="flex flex-col gap-2" 
        :resolver="customResolver || generatedResolver" 
        :initial-values="modelValue" 
        @submit="handleSubmit"
        v-bind="$attrs"
        v-on="$attrs"
    >
        <slot></slot>
    </Form>
</template>

<script setup lang="ts">
import { computed, provide, ref } from 'vue';
import * as yup from 'yup';
import { yupResolver } from '@primevue/forms/resolvers/yup';
import { FormSubmitEvent } from '@primevue/forms';

interface FormField {
    name: string;
    label: string;
    required?: boolean;
}

interface Props {
    modelValue: Record<string, any>;
    errors?: Record<string, string>;
    resolver?: any;
    labelPosition?: 'top' | 'left';
    labelWidth?: number;
}

const props = withDefaults(defineProps<Props>(), {
    labelWidth: 192,
    labelPosition: 'left'
});

const emit = defineEmits(['submit', 'update:modelValue','update:errors']);

const clearFormError = (field: string) => {
    if (props.errors) {
        const updatedErrors = { ...props.errors };
        if (Array.isArray(updatedErrors)) {
            const index = updatedErrors.indexOf(field);
            if (index !== -1) {
                updatedErrors.splice(index, 1);
            }
        } else if (updatedErrors[field]) {
            delete updatedErrors[field];
        }
        console.log(updatedErrors);
        emit('update:errors', updatedErrors);
    }
};

const formFields = ref<FormField[]>([]);

provide('registerFormField', (field: FormField) => {
    formFields.value.push(field);
});

provide('formConfig', computed(() => ({
    labelPosition: props.labelPosition,
    labelWidth: props.labelWidth
})));

provide('formErrors',computed(()=>props.errors));
provide('clearFormError',clearFormError);

const customResolver = computed(() => props.resolver);
const generatedResolver = computed(() => {
    const schema: Record<string, any> = {};
    formFields.value.forEach((field) => {
        if (field.required) {
            schema[field.name] = yup.string().required(`${field.label} is required`);
        }
    });

    return Object.keys(schema).length > 0 ? yupResolver(yup.object().shape(schema)) : undefined;
});

const handleSubmit = (event: FormSubmitEvent) => {
    emit('submit', event);
};
</script>