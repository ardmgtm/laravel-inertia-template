<template>
    <Dialog :header="(!editMode ? 'Add' : 'Edit') + ' User'" v-model:visible="dialogVisible" class="w-full max-w-2xl" :draggable="false"
        modal>
        <AppForm class="flex flex-col gap-4" v-model="formData" v-model:errors="formErrors" :resolver
            @submit="(e) => !editMode ? addSubmitAction(e) : editSubmitAction(e)">
            
            <!-- Basic Information Section -->
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="pi pi-user text-primary"></i>
                    Basic Information
                </h3>
                <div class="space-y-3">
                    <AppFormField name="name" label="Name" required>
                        <AppFormInput id="name" placeholder="Enter full name" v-model="formData.name" type="text" />
                    </AppFormField>
                    <AppFormField name="email" label="Email" required>
                        <AppFormInput id="email" placeholder="Enter email address" v-model="formData.email" type="email" />
                    </AppFormField>
                    <AppFormField name="username" label="Username" required>
                        <AppFormInput id="username" placeholder="Enter username" v-model="formData.username" type="text" />
                    </AppFormField>
                    <AppFormField name="role" label="User Role">
                        <MultiSelect id="role" v-model="formData.roles" :options="roleOptions" option-value="id"
                            option-label="name" placeholder="Select user roles" fluid display="chip" :max-selected-labels="2" />
                    </AppFormField>
                </div>
            </div>

            <!-- Security Section -->
            <div v-if="!editMode" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="pi pi-shield text-primary"></i>
                    Security
                </h3>
                <div class="space-y-3">
                    <AppFormField name="password" label="Password" required>
                        <div class="flex gap-2">
                            <Password id="password" placeholder="Enter password" v-model="formData.password" :feedback="false"
                                autocomplete="off" toggleMask fluid class="flex-1" />
                            <Button type="button" icon="pi pi-refresh" label="Generate" @click="generateStrongPassword"
                                severity="secondary" outlined size="small" />
                        </div>
                    </AppFormField>

                    <!-- Password Strength Indicator -->
                    <div v-if="formData.password">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Password Strength</span>
                            <span class="text-sm font-semibold" :class="strengthColor">
                                {{ strengthText }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-300" :class="strengthBarColor"
                                :style="{ width: strengthPercentage + '%' }"></div>
                        </div>
                    </div>

                    <!-- Password Requirements Checklist -->
                    <div class="p-3 bg-white rounded-lg border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Password Requirements</h4>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <i :class="passwordChecks.minLength ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                    class="text-xs"></i>
                                <span class="text-xs" :class="passwordChecks.minLength ? 'text-green-700' : 'text-gray-600'">
                                    At least 8 characters
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i :class="passwordChecks.hasUpperCase ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                    class="text-xs"></i>
                                <span class="text-xs" :class="passwordChecks.hasUpperCase ? 'text-green-700' : 'text-gray-600'">
                                    At least one uppercase letter (A-Z)
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i :class="passwordChecks.hasLowerCase ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                    class="text-xs"></i>
                                <span class="text-xs" :class="passwordChecks.hasLowerCase ? 'text-green-700' : 'text-gray-600'">
                                    At least one lowercase letter (a-z)
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i :class="passwordChecks.hasNumber ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                    class="text-xs"></i>
                                <span class="text-xs" :class="passwordChecks.hasNumber ? 'text-green-700' : 'text-gray-600'">
                                    At least one number (0-9)
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i :class="passwordChecks.hasSpecialChar ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                    class="text-xs"></i>
                                <span class="text-xs" :class="passwordChecks.hasSpecialChar ? 'text-green-700' : 'text-gray-600'">
                                    At least one special character (!@#$%^&*)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end w-full gap-2 mt-2">
                <Button label="Cancel" severity="secondary" @click.prevent="closeDialog" />
                <Button :label="!editMode ? 'Create' : 'Update'" severity="primary" type="submit" :loading="loading" />
            </div>
        </AppForm>
    </Dialog>
</template>
<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { reactive, Ref, ref, computed } from 'vue';
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

const formData = useForm<UserForm>({
    id: null,
    name: null,
    email: null,
    username: null,
    password: null,
    roles: null,
})
const formErrors = ref();

// Password strength validation checks
const passwordChecks = computed(() => {
    const password = formData.password || '';
    return {
        minLength: password.length >= 8,
        hasUpperCase: /[A-Z]/.test(password),
        hasLowerCase: /[a-z]/.test(password),
        hasNumber: /[0-9]/.test(password),
        hasSpecialChar: /[!@#$%^&*(),.?":{}|<>]/.test(password),
    };
});

// Calculate password strength
const passwordStrength = computed(() => {
    if (!formData.password) return 0;

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

// Generate strong password function
function generateStrongPassword() {
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const special = '!@#$%^&*()';
    
    // Ensure at least one character from each category
    let password = '';
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += special[Math.floor(Math.random() * special.length)];
    
    // Fill the rest randomly to make it 12 characters total
    const allChars = uppercase + lowercase + numbers + special;
    for (let i = password.length; i < 12; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }
    
    // Shuffle the password to avoid predictable patterns
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    formData.password = password;
    
    toast.add({
        severity: 'success',
        summary: 'Password Generated',
        detail: 'A strong password has been generated',
        life: 2000
    });
}

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
        axios.post(route('api.user.create'), formData)
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
        axios.put(route('api.user.update', { id: formData.id }), formData)
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
            axios.delete(route('api.user.delete', { id: data.id }))
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