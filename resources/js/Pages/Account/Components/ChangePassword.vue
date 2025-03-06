<template>
    <div class="col-span-12 lg:col-span-4 p-4 flex-1 border border-gray-200 rounded-md">
        <h2 class="text-2xl font-bold">Change Password</h2>
        <Divider />
        <AppForm v-model="formData" @submit="submitAction">
            <AppFormField name="old_password" label="Old Password" required>
                <Password id="old_password" v-model="formData.old_password" :feedback="false"
                    autocomplete="new-password" toggleMask fluid />
            </AppFormField>
            <AppFormField name="new_password" label="New Password" required>
                <Password id="new_password" v-model="formData.new_password" :feedback="false"
                    autocomplete="new-password" toggleMask fluid />
            </AppFormField>
            <AppFormField name="confirm_password" label="Confirm Password" required>
                <Password id="confirm_password" v-model="formData.confirm_password" :feedback="false"
                    autocomplete="new-password" toggleMask fluid />
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
import { ref } from 'vue';

const loading = ref<boolean>(false);
const formData = ref({
    old_password: '',
    new_password: '',
    confirm_password: '',
});

function submitAction() {
    console.log(formData.value);
}

</script>