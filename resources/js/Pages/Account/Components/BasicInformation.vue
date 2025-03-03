<template>
    <div class="col-span-12 lg:col-span-4 p-4 flex-1 border border-gray-200 rounded-md">
        <h2 class="text-2xl font-bold">Basic Information</h2>
        <Divider />
        <div class="flex flex-row gap-8 w-full">
            <div class="flex-none">
                <div class="relative w-40 h-40 mx-auto group">
                    <img :src="profilePicture ?? ''" alt="Profile"
                        class="w-full h-full rounded-full object-cover border shadow-sm" />

                    <div class="absolute bottom-1 right-1 bg-white rounded-full p-1 shadow-lg cursor-pointer w-7 h-7"
                        @click="uploadImage">
                        <i class="pi pi-pencil text-gray-600"></i>
                    </div>
                    <button v-if="profilePicture"
                        class="absolute top-1 right-1 bg-primary text-white rounded-full p-1 shadow opacity-0 group-hover:opacity-100 transition w-7 h-7"
                        @click="removeImage">
                        <i class="pi pi-undo"></i>
                    </button>
                    <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="handleFileChange" />
                </div>
            </div>
            <div class="flex-1">
                <AppForm v-model="formData" @submit="editSubmitAction">
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
                        <Button label="Update" type="submit" icon="pi pi-save" />
                    </AppFormField>
                </AppForm>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import AppForm from '@/Components/AppForm/AppForm.vue';
import AppFormField from '@/Components/AppForm/AppFormField.vue';
import AppFormInput from '@/Components/AppForm/AppFormInput.vue';
import { User, UserForm } from '@/Core/Models/user';
import { useForm, usePage } from '@inertiajs/vue3';
import { FormSubmitEvent } from '@primevue/forms';
import { onBeforeMount, ref } from 'vue';

const formData = useForm<UserForm>({
    name: null,
    username: null,
    email: null,
    profilePicture: null,
})

onBeforeMount(() => {
    const user = usePage().props.auth.user as User;
    formData.name = user.name;
    formData.username = user.username;
    formData.email = user.email;

    currentProfilePicture.value = 'https://primefaces.org/cdn/primevue/images/organization/walter.jpg';
    profilePicture.value = 'https://primefaces.org/cdn/primevue/images/organization/walter.jpg';
});

const currentProfilePicture = ref<string|null>(null);
const profilePicture = ref<string | null>(null);
const fileInput = ref<any>(null);

const uploadImage = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

const handleFileChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        formData.profilePicture = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            profilePicture.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    profilePicture.value = currentProfilePicture.value;
    formData.profilePicture = null;
    fileInput.value.value = '';
};

function editSubmitAction(event: FormSubmitEvent) {
    if (event.valid) {
        formData.post(route('account.update_information'), {
            preserveScroll: true,
            onSuccess: (response: any) => {
                console.log(response);
            },
        });
    }
}
</script>