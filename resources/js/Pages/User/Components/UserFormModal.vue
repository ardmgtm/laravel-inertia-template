<template>
    <Dialog :header="(!editMode ? 'Add' : 'Edit') + ' User'" v-model:visible="dialogVisible" class="w-full max-w-xl"
        modal>
        <AppForm class="flex flex-col gap-2" v-model="formData" v-model:errors="formErrors" :resolver
            @submit="(e) => !editMode ? addSubmitAction(e) : editSubmitAction(e)">
            <AppFormField name="name" label="Name" required>
                <AppFormInput id="name" placeholder="Name" v-model="formData.name" type="text" />
            </AppFormField>
            <AppFormField name="email" label="Email" required>
                <AppFormInput id="email" placeholder="Email" v-model="formData.email" type="email" />
            </AppFormField>
            <AppFormField name="username" label="Username" required>
                <AppFormInput id="username" placeholder="Username" v-model="formData.username" type="text" />
            </AppFormField>
            <AppFormField name="password" label="Password" required v-if="!editMode">
                <Password id="password" placeholder="Password" v-model="formData.password" :feedback="false"
                    autocomplete="off" toggleMask fluid />
            </AppFormField>
            <AppFormField name="role" label="User Role">
                <MultiSelect id="role" v-model="formData.roles" :options="roleOptions" option-value="id"
                    option-label="name" placeholder="Select Role" fluid display="chip" :max-selected-labels="2" />
            </AppFormField>
            <div class="flex justify-end w-full gap-2 mt-2">
                <Button label="Cancel" severity="secondary" @click.prevent="closeDialog" />
                <Button :label="!editMode ? 'Create' : 'Update'" severity="primary" type="submit" :loading="loading" />
            </div>
        </AppForm>
    </Dialog>
</template>
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { reactive, Ref, ref } from 'vue';
import { yupResolver } from '@primevue/forms/resolvers/yup';
import * as yup from 'yup';
import { FormSubmitEvent } from '@primevue/forms';
import { useConfirm, useToast } from 'primevue';
import { User, UserForm } from '@/Core/Models/user';
import { FormModalExpose } from '@/Core/Models/form-modal';
import AppForm from '@/Components/AppForm/AppForm.vue';
import AppFormField from '@/Components/AppForm/AppFormField.vue';
import AppFormInput from '@/Components/AppForm/AppFormInput.vue';
import { UserRole } from '@/Core/Models/user-role';
import axios from 'axios';

const toast = useToast();
const confirm = useConfirm();

const emit = defineEmits(['data-created', 'data-updated', 'data-deleted']);

const dialogVisible: Ref<boolean> = ref(false);
const editMode: Ref<boolean> = ref(false);
const loading: Ref<boolean> = ref(false);

const roleOptions = ref<UserRole[]>(usePage().props.roles as UserRole[]);

const formData = reactive<UserForm>({
    id: null,
    name: null,
    email: null,
    username: null,
    password: null,
    roles: null,
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

function closeDialog() {
    dialogVisible.value = false;
}
function addAction() {
    dialogVisible.value = true;
    editMode.value = false;
    formErrors.value = [];

    formData.id = null;
    formData.name = '';
    formData.email = '';
    formData.username = '';
    formData.password = '';
    formData.roles = [];
}
function addSubmitAction(event: FormSubmitEvent) {
    formErrors.value = {};
    if (event.valid) {
        loading.value = true;
        axios.post(route('user.create'), formData)
            .then((response) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.data.message,
                    life: 3000,
                });
                closeDialog();
                emit('data-created');
            })
            .catch((error) => {
                formErrors.value = error.response.data.errors;
                toast.add({
                    severity: 'error',
                    summary: 'Failed',
                    detail: error.response.data.message ?? 'Failed to create user!',
                    life: 3000,
                });
            })
            .finally(() => {
                loading.value = false;
            });
    }
}
function editAction(data: User) {
    dialogVisible.value = true;
    editMode.value = true;
    formErrors.value = [];

    formData.id = data.id;
    formData.name = data.name;
    formData.email = data.email;
    formData.username = data.username;
    formData.roles = data.roles?.map(role => role.id);
}
function editSubmitAction(event: FormSubmitEvent) {
    if (event.valid) {
        loading.value = true;
        axios.put(route('user.update', { id: formData.id }), formData)
            .then((response) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.data.message,
                    life: 3000,
                });
                closeDialog();
                emit('data-updated');
            })
            .catch((error) => {
                formErrors.value = error.response.data.errors;
                if (error.response.data.message) {
                    toast.add({
                        severity: 'error',
                        summary: 'Failed',
                        detail: error.response.data.message,
                        life: 3000,
                    });
                }
            })
            .finally(() => {
                loading.value = false;
            });
    }
}
function deleteAction(data: User) {
    confirm.require({
        message: 'Do you want to delete this user ?',
        header: 'Warning',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            loading.value = true;
            axios.delete(route('user.delete', { id: data.id }))
                .then((response) => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: response.data.message,
                        life: 3000,
                    });
                    emit('data-deleted');
                })
                .catch((error) => {
                    if (error.response.data.message) {
                        toast.add({
                            severity: 'error',
                            summary: 'Failed',
                            detail: error.response.data.message,
                            life: 3000,
                        });
                    }
                })
                .finally(() => {
                    loading.value = false;
                });
        },
        reject: () => {
            toast.add({
                severity: 'info',
                summary: 'Info',
                detail: 'Action Canceled',
                life: 3000
            });
        }
    });
}

defineExpose<FormModalExpose<User>>({
    addAction,
    editAction,
    deleteAction,
});
</script>