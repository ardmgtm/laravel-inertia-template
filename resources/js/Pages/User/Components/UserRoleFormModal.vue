<template>

</template>
<script setup lang="ts">
import { FormModalExpose } from '@/Core/Models/form-modal';
import { UserRole, UserRoleForm } from '@/Core/Models/user-role';
import { router, useForm } from '@inertiajs/vue3';
import { FormSubmitEvent } from '@primevue/forms';
import { useConfirm, useToast } from 'primevue';
import { ref } from 'vue';

const toast = useToast();
const confirm = useConfirm();

// CRUD user role
const dialogFormRoleVisible = ref(false);
const editMode = ref(false);
const formData = useForm<UserRoleForm>({
    id: null,
    name: null,
});

function closeDialog() {
    dialogFormRoleVisible.value = false;
}

function addAction() {
    dialogFormRoleVisible.value = true;
    editMode.value = false;

    formData.id = null;
    formData.name = null;
}
function addActionSubmit(event: FormSubmitEvent) {
    if (event.valid) {
        dialogFormRoleVisible.value = false;
        formData.post(route('role.create'), {
            onSuccess: (response: any) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.props.flash.message,
                    life: 1000,
                });
                router.reload({ only: ['roles'] });
            },
            onError: (errors) => {
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
    dialogFormRoleVisible.value = true;
    editMode.value = true;

    formData.id = dataRole.id;
    formData.name = dataRole.name;
}
async function editActionSubmit(event: FormSubmitEvent) {
    if (event.valid) {
        dialogFormRoleVisible.value = false;
        formData.put(route('role.update', { id: formData.id }), {
            onSuccess: (response: any) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.props.flash.message,
                    life: 3000,
                });
                router.reload({ only: ['roles'] });
            },
            onError: (errors) => {
                console.log(errors);
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
        message: 'Apakah anda yakin untuk mengahapus user ini ?',
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