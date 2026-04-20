<template>
    <div>
        <Head title="Login" />
        <Toast />

        <div
            class="min-h-screen flex items-center justify-center relative overflow-hidden bg-gradient-to-br from-blue-50 via-blue-100 to-blue-200 dark:from-gray-900 dark:via-blue-900 dark:to-blue-800 px-4 py-8"
        >
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div
                    class="absolute -top-40 -left-40 w-80 h-80 bg-blue-300 dark:bg-blue-700 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-70 animate-blob"
                ></div>
                <div
                    class="absolute -top-40 -right-40 w-80 h-80 bg-blue-400 dark:bg-blue-600 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-70 animate-blob animation-delay-2000"
                ></div>
                <div
                    class="absolute -bottom-40 left-20 w-80 h-80 bg-blue-200 dark:bg-blue-800 rounded-full mix-blend-multiply dark:mix-blend-soft-light filter blur-xl opacity-70 animate-blob animation-delay-4000"
                ></div>
            </div>

            <div class="w-full max-w-5xl relative z-10">
                <div
                    class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl shadow-2xl rounded-3xl overflow-hidden border border-white/20 dark:border-gray-700/50 transform hover:scale-[1.01] transition-all duration-300"
                >
                    <div class="flex flex-col lg:flex-row">
                        <div
                            class="w-full lg:w-1/2 hidden lg:flex flex-col justify-center items-center p-12 bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 dark:from-blue-700 dark:via-blue-800 dark:to-blue-900 relative overflow-hidden"
                        >
                            <div class="absolute inset-0 bg-[url('/vite.svg')] bg-contain bg-no-repeat bg-center opacity-20"></div>
                            <div class="relative z-10 text-white text-center space-y-6">
                                <div class="animate-float">
                                    <svg class="w-32 h-32 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        />
                                    </svg>
                                </div>
                                <h2 class="text-4xl font-bold drop-shadow-lg">Selamat Datang!</h2>
                                <p class="text-lg opacity-90 max-w-md mx-auto">Silakan login untuk melanjutkan</p>
                                <div class="flex gap-2 justify-center mt-8">
                                    <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                                    <div class="w-3 h-3 bg-white rounded-full animate-pulse animation-delay-200"></div>
                                    <div class="w-3 h-3 bg-white rounded-full animate-pulse animation-delay-400"></div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full lg:w-1/2 p-8 md:p-12">
                            <div class="text-center mb-8 space-y-4">
                                <div class="flex items-center justify-center animate-fade-in">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full blur-lg opacity-50"></div>
                                        <img src="/vite.svg" class="h-20 relative z-10 drop-shadow-xl" alt="App Logo" />
                                    </div>
                                </div>
                                <h1
                                    class="text-gray-800 dark:text-white text-4xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent"
                                >
                                    Welcome Back
                                </h1>
                                <p class="text-gray-600 dark:text-gray-300 text-base font-medium">Sign in to continue</p>
                            </div>

                            <Form class="flex flex-col gap-5" @submit="loginAction" :resolver="resolver">
                                <FormField name="username" v-slot="$field">
                                    <label class="block text-gray-700 dark:text-gray-200 font-semibold mb-2" for="username">
                                        <i class="pi pi-user mr-2 text-blue-500"></i>Username
                                    </label>
                                    <InputText
                                        id="username"
                                        placeholder="Masukkan username Anda"
                                        v-model="formSignIn.username"
                                        type="text"
                                        class="w-full transition-all duration-300 hover:border-blue-400 focus:border-blue-500"
                                        autocomplete="username"
                                        fluid
                                    />
                                    <Message class="mt-2" severity="error" size="small" variant="simple">{{ $field.error?.message }}</Message>
                                </FormField>

                                <FormField name="password" v-slot="$field">
                                    <label class="block text-gray-700 dark:text-gray-200 font-semibold mb-2" for="password">
                                        <i class="pi pi-lock mr-2 text-blue-500"></i>Password
                                    </label>
                                    <Password
                                        id="password"
                                        placeholder="Masukkan password Anda"
                                        v-model="formSignIn.password"
                                        :feedback="false"
                                        toggleMask
                                        fluid
                                        class="w-full transition-all duration-300"
                                        autocomplete="current-password"
                                    />
                                    <Message class="mt-2" severity="error" size="small" variant="simple">{{ $field.error?.message }}</Message>
                                </FormField>

                                <Button
                                    type="submit"
                                    label="Masuk"
                                    icon="pi pi-sign-in"
                                    class="w-full mt-4 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 border-0 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300"
                                    :loading="loading"
                                    size="large"
                                />
                            </Form>

                            <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                <p>Butuh bantuan? Hubungi administrator sistem</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-center py-4 shadow-inner">
            <p class="text-sm">© {{ currentYear }} Your Company. All rights reserved.</p>
        </footer>
    </div>
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

const loading = ref(false);

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
                authStore.setUser(response.props.flash.user);
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

<style scoped>
@keyframes blob {
    0%,
    100% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

.animate-fade-in {
    animation: fade-in 0.8s ease-out;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.animation-delay-200 {
    animation-delay: 0.2s;
}

.animation-delay-400 {
    animation-delay: 0.4s;
}
</style>
