<template>
    <Dialog :header="(!editMode ? 'Add' : 'Edit') + ' User Role'" v-model:visible="dialogVisible"
        class="w-full max-w-xl" modal>
        <AppForm v-model="formData" label-position="left" :label-width="192" v-model:errors="formErrors"
            @submit="(e) => !editMode ? addSubmitAction(e) : editSubmitAction(e)">
            <AppFormField name="name" label="Name" required>
                <AppFormInput id="name" v-model="formData.name"/>
            </AppFormField>

            <div class="flex justify-end w-full gap-2 mt-4">
                <Button label="Cancel" severity="secondary" outlined @click.prevent="closeDialog" />
                <Button :label="!editMode ? 'Create' : 'Update'" severity="primary" type="submit" :loading="loading" />
            </div>
        </AppForm>
    </Dialog>
</template>
<script setup lang="ts">
import AppForm from '@/Components/AppForm/AppForm.vue';
import AppFormField from '@/Components/AppForm/AppFormField.vue';
import AppFormInput from '@/Components/AppForm/AppFormInput.vue';
import { FormModalExpose } from '@/Core/Models/form-modal';
import { UserRole, UserRoleForm } from '@/Core/Models/user-role';
import { router, useForm } from '@inertiajs/vue3';
import { FormSubmitEvent } from '@primevue/forms';
import { useConfirm, useToast } from 'primevue';
import { ref } from 'vue';

const toast = useToast();
const confirm = useConfirm();

const dialogVisible = ref(false);
const editMode = ref(false);
const loading = ref(false);
const formData = useForm<UserRoleForm>({
    id: null,
    name: null,
});
const formErrors = ref();

function closeDialog() {
    dialogVisible.value = false;
}

function addAction() {
    dialogVisible.value = true;
    editMode.value = false;
    formErrors.value = {};

    formData.reset();
}
function addSubmitAction(event: FormSubmitEvent) {
    formErrors.value = {};
    if (event.valid) {
        formData.post(route('role.create'), {
            onSuccess: (response: any) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.props.flash.message,
                    life: 1000,
                });
                dialogVisible.value = false;
            },
            onError: (errors) => {
                formErrors.value = errors;
                toast.add({
                    severity: 'error',
                    summary: 'Failed',
                    detail: errors.message ?? 'Failed to create user role !',
                    life: 3000,
                });
            }
        });
    }
}
function editAction(dataRole: UserRole) {
    dialogVisible.value = true;
    editMode.value = true;
    formErrors.value = {};

    formData.id = dataRole.id;
    formData.name = dataRole.name;
}
async function editSubmitAction(event: FormSubmitEvent) {
    formErrors.value = {};
    if (event.valid) {
        formData.put(route('role.update', { id: formData.id }), {
            onSuccess: (response: any) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.props.flash.message,
                    life: 3000,
                });
                dialogVisible.value = false;
            },
            onError: (errors) => {
                formErrors.value = errors;
                toast.add({
                    severity: 'error',
                    summary: 'Failed',
                    detail: errors.message ?? 'Failed to update role !',
                    life: 3000,
                });
            }
        });
    }
}
function deleteAction(dataRole: UserRole) {
    confirm.require({
        message: 'Do you want to delete this user role ?',
        header: 'Warning',
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'OK',
            severity: 'danger'
        },
        accept: () => {
            router.delete(route('role.delete', { id: dataRole.id }), {
                onSuccess: (response: any) => {
                    toast.add({
                        severity: 'success',
                        summary: 'Success',
                        detail: response.props.flash.message,
                        life: 3000
                    });
                },
                onError: (errors) => {
                    toast.add({
                        severity: 'error',
                        summary: 'Failed',
                        detail: errors.message,
                        life: 3000
                    });
                }
            });
        },
        reject: () => {
            toast.add({
                severity: 'info',
                summary: 'Info',
                detail: 'Action canceled',
                life: 3000
            });
        }
    });
}

defineExpose<FormModalExpose<UserRole>>({
    addAction,
    editAction,
    deleteAction,
})
</script>