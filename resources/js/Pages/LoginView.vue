<template>
    <Head title="Login" />
    <Toast/>
    <div class="bg-surface-200 dark:bg-surface-950 px-6 py-20 md:px-12 lg:px-20 h-screen">
        <div class="bg-surface-0 dark:bg-surface-900 p-6 shadow-lg rounded-border max-w-3xl mx-auto my-auto">
            <div class="flex flex-col lg:flex-row">
                <div class="w-full lg:w-1/2 hidden lg:block rounded-l-lg bg-[url('/images/login-illustrration.jpg')] bg-contain bg-no-repeat bg-center">
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="text-center mb-8">
                        <div class="flex items-center justify-center">
                            <img src="/vite.svg" class="h-16" alt="App Logo"/>
                        </div>
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Welcome Back</div>
                    </div>
        
                    <Form class="flex flex-col gap-2" @submit="loginAction" :resolver="resolver">
                        <FormField name="username"  v-slot="$field">
                            <label class="font-bold" for="username">Username</label>
                            <InputText id="username" placeholder="Username" v-model="formSignIn.username" type="text" class="w-full" fluid/>
                            <Message class="h-2 mt-2" severity="error" size="small" variant="simple">{{ $field.error?.message }}</Message>
                        </FormField>
                        
                        <FormField name="password" v-slot="$field">
                            <label class="font-bold" for="password">Password</label>
                            <Password id="password" placeholder="Password" v-model="formSignIn.password" :feedback="false" toggleMask fluid />
                            <Message class="h-2 mt-2" severity="error" size="small" variant="simple">{{ $field.error?.message }}</Message>
                        </FormField>
        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <Checkbox id="remember" name="remember" v-model="formSignIn.remember" :binary="true" class="mr-2" />
                                <label for="remember">Remember me</label>
                            </div>
                        </div>
                        <Button type="submit" label="Login" icon="pi pi-send" class="w-full" :loading="loading"/>
                    </Form>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-gray-900 text-white text-center py-4 fixed bottom-0 w-full text-sm">
        © {{ currentYear }} Your Company. All rights reserved.
    </footer>
</template>
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from "primevue/usetoast";
import { Form, FormSubmitEvent } from '@primevue/forms';
import { yupResolver } from '@primevue/forms/resolvers/yup';
import * as yup from 'yup';
import { useAuthStore } from '@/Stores/auth-store';

const toast = useToast();
const authStore = useAuthStore();

const currentYear = new Date().getFullYear();
const formSignIn = useForm({
    username : null,
    password : null,
    remember : false,
})
const resolver = yupResolver(
    yup.object().shape({
        username: yup.string().required('Username is required'),
        password: yup.string().required('Password is required')
    })
);

const showPassword = ref(false);
const loading = ref(false);

function togglePassword() {
    showPassword.value = !showPassword.value;
}

function loginAction(event: FormSubmitEvent) {
    if(event.valid){
        loading.value = true;
        formSignIn.post(route('login'),{
            preserveScroll: true,
            onSuccess: (response: any) => {
                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Login Successful',
                    life: 3000,
                });
                authStore.setRoles(response.props.flash.roles);
                authStore.setPermissions(response.props.flash.permissions);
            },
            onError: (errors) => {
                if(errors.message){
                    toast.add({ 
                        severity: 'error', 
                        summary: 'Not Authenticated', 
                        detail: errors.message,
                        life: 3000,
                    });
                }
            },
            onFinish: () => {
                loading.value = false;
            }   
        });
    }
}

</script>
