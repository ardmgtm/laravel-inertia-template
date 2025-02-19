<template>
    <Dialog :header="(!editMode ? 'Add' : 'Edit') + ' User'" v-model:visible="dialogVisible" class="w-full max-w-xl"
        modal>
        <Form class="flex flex-col gap-2" :resolver :initialValues="formData"
            @submit="(e) => !editMode ? addSubmitAction(e) : editSubmitAction(e)">
            <FormField name="name" v-slot="$field" class="flex">
                <label class="flex-none font-bold w-48" for="name">Name</label>
                <div class="flex-1">
                    <InputText id="name" placeholder="Name" v-model="formData.name" type="text" class="w-full" fluid
                        @input="clearError('name')" />
                    <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                        {{ $field.error?.message ?? formErrors?.name }}
                    </Message>
                </div>
            </FormField>
            <FormField name="email" v-slot="$field" class="flex">
                <label class="flex-none font-bold w-48" for="email">Email</label>
                <div class="flex-1">
                    <InputText id="email" placeholder="Email" v-model="formData.email" type="text" class="w-full"
                        autocomplete="off" fluid @input="clearError('email')" />
                    <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                        {{ $field.error?.message ?? formErrors?.email }}
                    </Message>
                </div>
            </FormField>
            <FormField name="username" v-slot="$field" class="flex">
                <label class="flex-none font-bold w-48" for="username">Username</label>
                <div class="flex-1">
                    <InputText id="username" placeholder="Username" v-model="formData.username" type="text"
                        autocomplete="off" class="w-full" fluid @input="clearError('username')" />
                    <Message class="h-2 mt-2" severity="error" size="small" variant="simple">
                        {{ $field.error?.message ?? formErrors?.username }}
                    </Message>
                </div>
            </FormField>
            <FormField name="password" v-slot="$field" class="flex" v-if="!editMode">
                <label class="flex-none font-bold w-48" for="password">Password</label>
                <div class="flex-1">
                    <Password id="password" placeholder="Password" v-model="formData.password" :feedback="false"
                        autocomplete="off" class="w-full" toggleMask fluid @input="clearError('password')" />
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
import { router, useForm } from '@inertiajs/vue3';
import { Ref, ref } from 'vue';
import { yupResolver } from '@primevue/forms/resolvers/yup';
import * as yup from 'yup';
import { FormSubmitEvent } from '@primevue/forms';
import { useConfirm, useToast } from 'primevue';
import { User, UserForm } from '@/Core/Models/user';
import { FormModalExpose } from '@/Core/Models/form-modal';

const toast = useToast();
const confirm = useConfirm();

const emit = defineEmits(['data-change'])

const dialogVisible : Ref<boolean> = ref(false);
const editMode : Ref<boolean> = ref(false);
const loading : Ref<boolean> = ref(false);

const formData = useForm<UserForm>({
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
                emit('data-change');
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
                emit('data-change');
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
                    emit('data-change');
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