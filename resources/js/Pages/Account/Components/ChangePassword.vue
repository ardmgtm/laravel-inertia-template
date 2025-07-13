<template>
    <div class="col-span-12 lg:col-span-4 p-4 flex-1 border border-gray-200 rounded-md">
        <h2 class="text-2xl font-bold">Change Password</h2>
        <Divider />
        <AppForm v-model="formData" v-model:errors="formErrors" @submit="submitAction">
            <AppFormField name="old_password" label="Old Password" required>
                <Password id="old_password" v-model="formData.old_password" :feedback="false" autocomplete="off"
                    toggleMask fluid />
            </AppFormField>
            <AppFormField name="new_password" label="New Password" required>
                <Password id="new_password" v-model="formData.new_password" :feedback="false" autocomplete="off"
                    toggleMask fluid />
            </AppFormField>
            <AppFormField name="confirm_password" label="Confirm Password" required>
                <Password id="confirm_password" v-model="formData.confirm_password" :feedback="false" autocomplete="off"
                    toggleMask fluid />
            </AppFormField>
            <AppFormField>
                <div class="flex gap-4">
                    <Button label="Update" type="submit" icon="pi pi-save" :loading />
                </div>
            </AppFormField>
        </AppForm>
    </div>
</template>
<script setup lang="ts">
import AppForm from '@/Components/AppForm/AppForm.vue';
import AppFormField from '@/Components/AppForm/AppFormField.vue';
import { useForm } from '@inertiajs/vue3';
import { FormSubmitEvent } from '@primevue/forms';
import { useToast } from 'primevue';
import { ref } from 'vue';
import axios from 'axios';

const toast = useToast();

const loading = ref<boolean>(false);
const formData = useForm({
    old_password: '',
    new_password: '',
    confirm_password: '',
});
const formErrors = ref();

function submitAction(event: FormSubmitEvent) {
    if (event.valid) {
        loading.value = true;
        formErrors.value = null;
        axios.post(route('account.change_password'), {
            old_password: formData.old_password,
            new_password: formData.new_password,
            confirm_password: formData.confirm_password,
        })
            .then(response => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: response.data?.message || 'Password changed successfully',
                    life: 3000
                });
                formData.old_password = '';
                formData.new_password = '';
                formData.confirm_password = '';
            })
            .catch(error => {
                if (error.response && error.response.data && error.response.data.errors) {
                    formErrors.value = error.response.data.errors;
                }
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: error.response?.data?.message || 'An error occurred',
                    life: 3000
                });
            })
            .finally(() => {
                loading.value = false;
            });
    }
}

</script>