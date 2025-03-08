<template>
    <div class="col-span-12 lg:col-span-4 p-4 flex-1 border border-gray-200 rounded-md">
        <h2 class="text-2xl font-bold">Basic Information</h2>
        <Divider />
        <div class="flex flex-row gap-8 w-full">
            <div class="flex-none">
                <div class="relative w-40 h-40 mx-auto group">
                    <AppProfilePicture :name="user?.name" :size="160" :url="(profilePicture as string)"
                        class="w-full h-full rounded-full object-cover border shadow-sm" />
                    <div class="absolute bottom-1 right-1 bg-white rounded-full p-1 shadow-lg cursor-pointer w-7 h-7"
                        @click="uploadImage" v-if="editMode">
                        <i class="pi pi-pencil text-gray-600"></i>
                    </div>
                    <button v-if="profilePicture && editMode && profilePicture !== currentProfilePicture"
                        class="absolute top-1 right-1 bg-primary text-white rounded-full p-1 shadow opacity-0 group-hover:opacity-100 transition w-7 h-7"
                        @click="removeImage">
                        <i class="pi pi-undo"></i>
                    </button>
                    <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileChange" />
                </div>
            </div>
            <div class="flex-1">
                <AppForm v-model="formData" @submit="editSubmitAction" v-if="editMode">
                    <AppFormField name="name" label="Name" required>
                        <AppFormInput id="name" placeholder="Name" v-model="formData.name" type="text" />
                    </AppFormField>
                    <AppFormField name="email" label="Email" required>
                        <AppFormInput id="email" placeholder="Email" v-model="formData.email" type="email" />
                    </AppFormField>
                    <AppFormField name="username" label="Username" required>
                        <AppFormInput id="username" placeholder="Username" v-model="formData.username" type="text" />
                    </AppFormField>
                    <AppFormField>
                        <div class="flex gap-4">
                            <Button label="Update" type="submit" icon="pi pi-save" :loading />
                            <Button label="Cancel" severity="primary" variant="outlined" @click="editMode = false" />
                        </div>
                    </AppFormField>
                </AppForm>
                <div v-else>
                    <table class="table-auto w-full text-left text-lg">
                        <tbody>
                            <tr>
                                <th class="font-semibold w-32">Name</th>
                                <td>: {{ user?.name }}</td>
                            </tr>
                            <tr>
                                <th class="font-semibold w-32">Email</th>
                                <td>: {{ user?.email }}</td>
                            </tr>
                            <tr>
                                <th class="font-semibold w-32">Username</th>
                                <td>: {{ user?.username }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="flex justify-first mt-4">
                        <Button label="Edit Information" icon="pi pi-pencil" @click="editMode = true"
                            variant="outlined" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import AppProfilePicture from '@/Components/AppProfilePicture.vue';
import AppForm from '@/Components/AppForm/AppForm.vue';
import AppFormField from '@/Components/AppForm/AppFormField.vue';
import AppFormInput from '@/Components/AppForm/AppFormInput.vue';
import { User, UserForm } from '@/Core/Models/user';
import { FormSubmitEvent } from '@primevue/forms';
import { useToast } from 'primevue';
import { computed, onBeforeMount, reactive, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/Stores/auth-store';

const toast = useToast();
const editMode = ref<boolean>(false);
const loading = ref<boolean>(false);

const authStore = useAuthStore();
const user = computed<User | null>(() => authStore.user);

const formData = reactive<UserForm>({
    name: null,
    username: null,
    email: null,
    profile_picture: null,
})

onBeforeMount(() => {
    formData.name = user.value?.name;
    formData.username = user.value?.username;
    formData.email = user.value?.email;

    currentProfilePicture.value = user.value?.profile_picture;
    profilePicture.value = user.value?.profile_picture;
});

const currentProfilePicture = ref<string | undefined>();
const profilePicture = ref<string |undefined>();
const fileInput = ref<any>(null);

const uploadImage = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const handleFileChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        formData.profile_picture = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            profilePicture.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    profilePicture.value = currentProfilePicture.value;
    formData.profile_picture = null;
    fileInput.value.value = '';
};

function editSubmitAction(event: FormSubmitEvent) {
    if (event.valid) {
        loading.value = true;
        axios.post(route('account.update_information'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        }).then(response => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: response.data.message,
                life: 3000
            });
            authStore.setUser(response.data.data.user);
            editMode.value = false;
        }).catch(error => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response?.data?.message || 'An error occurred',
                life: 3000
            });
        }).finally(() => {
            loading.value = false;
        });
    }
}
</script>