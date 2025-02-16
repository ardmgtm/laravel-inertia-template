<template>
    <Dialog :header="(!editMode ? 'Add' : 'Edit') + ' User'" v-model:visible="dialogVisible" class="w-full max-w-xl"
        modal>
        <Form class="flex flex-col gap-2" :resolver
            @submit="(e) => !editMode ? addSubmitAction(e) : editSubmitAction(e)">
            <FormField name="name" v-slot="$field" class="flex">
                <label class="flex-none font-bold w-48" for="name">Name</label>
                <div class="flex-1">
                    <InputText id="name" placeholder="Name" v-model="formData.name" type="text" class="w-full" fluid
                        @change="clearError('name')" />
                    <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                        {{ $field.error?.message ?? formErrors?.name }}
                    </Message>
                </div>
            </FormField>
            <FormField name="email" v-slot="$field" class="flex">
                <label class="flex-none font-bold w-48" for="email">Email</label>
                <div class="flex-1">
                    <InputText id="email" placeholder="Email" v-model="formData.email" type="text" class="w-full"
                        autocomplete="off" fluid @change="clearError('email')" />
                    <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                        {{ $field.error?.message ?? formErrors?.email }}
                    </Message>
                </div>
            </FormField>
            <FormField name="username" v-slot="$field" class="flex">
                <label class="flex-none font-bold w-48" for="username">Username</label>
                <div class="flex-1">
                    <InputText id="username" placeholder="Username" v-model="formData.username" type="text"
                        autocomplete="off" class="w-full" fluid @change="clearError('username')" />
                    <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                        {{ $field.error?.message ?? formErrors?.username }}
                    </Message>
                </div>
            </FormField>
            <FormField name="password" v-slot="$field" class="flex">
                <label class="flex-none font-bold w-48" for="password">Password</label>
                <div class="flex-1">
                    <Password id="password" placeholder="Password" v-model="formData.password" :feedback="false"
                        autocomplete="off" class="w-full" toggleMask fluid @change="clearError('password')" />
                    <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                        {{ $field.error?.message ?? formErrors?.password }}
                    </Message>
                </div>
            </FormField>
            <div class="flex justify-end w-full gap-2 mt-2">
                <Button label="Cancel" severity="secondary" @click.prevent="closeDialog" />
                <Button :label="!editMode ? 'Create' : 'Update'" severity="primary" type="submit" :loading />
            </div>
        </Form>
    </Dialog>
</template>
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { yupResolver } from '@primevue/forms/resolvers/yup';
import * as yup from 'yup';
import { FormSubmitEvent } from '@primevue/forms';
import { useToast } from 'primevue';

const toast = useToast();

const dialogVisible = ref(false);
const editMode = ref(false);
const loading = ref(false);

const formData = useForm({
    id: null,
    name: null,
    email: null,
    username: null,
    password: null,
})
const formErrors = ref();
const resolver = yupResolver(
    yup.object().shape({
        name: yup.string().required('Name is required'),
        email: yup.string().required('Email is required').email('Not valid email format'),
        username: yup.string().required('Username is required'),
        password: yup.string().required('Password is required'),
    })
);

function clearError(field: string) {
    if (formErrors.value && formErrors.value[field]) {
        delete formErrors.value[field];
    }
}

function closeDialog() {
    dialogVisible.value = false;
}
function addAction() {
    dialogVisible.value = true;
    editMode.value = false;
    formData.reset();
}
function addSubmitAction(event: FormSubmitEvent) {
    formErrors.value = {};
    if (event.valid) {
        loading.value = true;
        formData.post(route('user.create'), {
            onSuccess: (response: any) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.props.flash.message,
                    life: 1000,
                });
                closeDialog();
            },
            onError: (errors) => {
                formErrors.value = errors;
                toast.add({
                    severity: 'error',
                    summary: 'Failed',
                    detail: errors.message ?? 'Failed to create user !',
                    life: 3000,
                });
            },
            onFinish: () => {
                loading.value = false;
            }
        })
    }
}
function editAction() {
    dialogVisible.value = true;
    editMode.value = false;
}
function editSubmitAction(event: FormSubmitEvent) {
    if (event.valid) {
        loading.value = true;
        formData.post(route('user.update', { id: formData.id }), {
            onSuccess: (response) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.props.message,
                    life: 1000,
                });
                closeDialog();
            },
            onError: (errors) => {
                if (errors.message) {
                    toast.add({
                        severity: 'error',
                        summary: 'Failed',
                        detail: errors.message,
                        life: 3000,
                    });
                }
            },
            onFinish: () => {
                loading.value = false;
            }
        })
    }
}
function deleteAction() {

}

defineExpose({
    addAction,
    editAction,
    deleteAction,
});
</script>