<template>
    <Dialog :header="(!editMode ? 'Add' : 'Edit') + ' User'" v-model:visible="dialogVisible" class="w-full max-w-xl"
        modal>
        <AppForm class="flex flex-col gap-2" v-model="formData" :errors="formErrors" :resolver
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
                <Select id="role" v-model="formData.roles" :options="roleOptions" option-value="id" option-label="name"
                    placeholder="Select Role" fluid :show-clear="true"/>
            </AppFormField>
            <div class="flex justify-end w-full gap-2 mt-2">
                <Button label="Cancel" severity="secondary" @click.prevent="closeDialog" />
                <Button :label="!editMode ? 'Create' : 'Update'" severity="primary" type="submit" :loading="loading" />
            </div>
        </AppForm>
    </Dialog>
</template>
<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { Ref, ref } from 'vue';
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

const toast = useToast();
const confirm = useConfirm();

const emit = defineEmits(['data-created', 'data-updated', 'data-deleted']);

const dialogVisible: Ref<boolean> = ref(false);
const editMode: Ref<boolean> = ref(false);
const loading: Ref<boolean> = ref(false);

const roleOptions = ref<UserRole[]>(usePage().props.roles as UserRole[]);

const formData = useForm<UserForm>({
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
                emit('data-created');
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
function editAction(data: User) {
    dialogVisible.value = true;
    editMode.value = true;

    formData.id = data.id;
    formData.name = data.name;
    formData.email = data.email;
    formData.username = data.username;
    formData.roles = data.roles?.[0]?.id;
}
function editSubmitAction(event: FormSubmitEvent) {
    if (event.valid) {
        loading.value = true;
        formData.put(route('user.update', { id: formData.id }), {
            onSuccess: (response: any) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.props.flash.message,
                    life: 1000,
                });
                closeDialog();
                emit('data-updated');
            },
            onError: (errors) => {
                formErrors.value = errors;
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
            router.delete(route('user.delete', { id: data.id }), {
                onSuccess: (response: any) => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: response.props.flash.message,
                        life: 3000,
                    });
                    emit('data-deleted');
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
                }
            })
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