<template>
    <div class="col-span-12 lg:col-span-4 p-6 flex-1 border border-gray-200 rounded-lg shadow-sm bg-white">
        <h2 class="text-2xl font-bold text-gray-800 mb-1">Change Password</h2>
        <p class="text-sm text-gray-500 mb-4">Update your password to keep your account secure</p>
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
                <!-- Password Strength Indicator -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Password Strength</span>
                        <span class="text-sm font-semibold" :class="strengthColor">
                            {{ strengthText }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all duration-300" :class="strengthBarColor"
                            :style="{ width: strengthPercentage + '%' }"></div>
                    </div>
                </div>
    
                <!-- Password Requirements Checklist -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Password Requirements</h3>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <i :class="passwordChecks.minLength ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                class="text-sm"></i>
                            <span class="text-sm" :class="passwordChecks.minLength ? 'text-green-700' : 'text-gray-600'">
                                At least 8 characters
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i :class="passwordChecks.hasUpperCase ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                class="text-sm"></i>
                            <span class="text-sm" :class="passwordChecks.hasUpperCase ? 'text-green-700' : 'text-gray-600'">
                                At least one uppercase letter (A-Z)
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i :class="passwordChecks.hasLowerCase ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                class="text-sm"></i>
                            <span class="text-sm" :class="passwordChecks.hasLowerCase ? 'text-green-700' : 'text-gray-600'">
                                At least one lowercase letter (a-z)
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i :class="passwordChecks.hasNumber ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                class="text-sm"></i>
                            <span class="text-sm" :class="passwordChecks.hasNumber ? 'text-green-700' : 'text-gray-600'">
                                At least one number (0-9)
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i :class="passwordChecks.hasSpecialChar ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                class="text-sm"></i>
                            <span class="text-sm"
                                :class="passwordChecks.hasSpecialChar ? 'text-green-700' : 'text-gray-600'">
                                At least one special character (!@#$%^&*)
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i :class="passwordChecks.passwordsMatch ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                class="text-sm"></i>
                            <span class="text-sm"
                                :class="passwordChecks.passwordsMatch ? 'text-green-700' : 'text-gray-600'">
                                Passwords match
                            </span>
                        </div>
                    </div>
                </div>
            </AppFormField>


            <AppFormField>
                <div class="flex gap-4">
                    <Button label="Update Password" type="submit" icon="pi pi-save" :loading
                        class="w-full justify-center" />
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
import { ref, computed } from 'vue';
import axios from 'axios';

const emit = defineEmits<{
    passwordChanged: []
}>();

const toast = useToast();

const loading = ref<boolean>(false);
const formData = useForm({
    old_password: '',
    new_password: '',
    confirm_password: '',
});
const formErrors = ref();

// Password strength validation checks
const passwordChecks = computed(() => {
    const password = formData.new_password;
    return {
        minLength: password.length >= 8,
        hasUpperCase: /[A-Z]/.test(password),
        hasLowerCase: /[a-z]/.test(password),
        hasNumber: /[0-9]/.test(password),
        hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(password),
        passwordsMatch: password.length > 0 && password === formData.confirm_password,
    };
});

// Calculate password strength
const passwordStrength = computed(() => {
    if (!formData.new_password) return 0;

    const checks = passwordChecks.value;
    let strength = 0;

    if (checks.minLength) strength += 20;
    if (checks.hasUpperCase) strength += 20;
    if (checks.hasLowerCase) strength += 20;
    if (checks.hasNumber) strength += 20;
    if (checks.hasSpecialChar) strength += 20;

    return strength;
});

// Strength text and colors
const strengthText = computed(() => {
    const strength = passwordStrength.value;
    if (strength <= 40) return 'Weak';
    if (strength <= 80) return 'Medium';
    return 'Strong';
});

const strengthColor = computed(() => {
    const strength = passwordStrength.value;
    if (strength <= 40) return 'text-red-600';
    if (strength <= 80) return 'text-yellow-600';
    return 'text-green-600';
});

const strengthBarColor = computed(() => {
    const strength = passwordStrength.value;
    if (strength <= 40) return 'bg-red-500';
    if (strength <= 80) return 'bg-yellow-500';
    return 'bg-green-500';
});

const strengthPercentage = computed(() => passwordStrength.value);

function submitAction(event: FormSubmitEvent) {
    if (event.valid) {
        loading.value = true;
        formErrors.value = null;
        axios.post(route('api.account.change_password'), {
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
                emit('passwordChanged');
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