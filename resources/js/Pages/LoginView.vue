<template>
    <Head title="Login" />
    <Toast/>
    <div class="min-h-screen bg-gradient-to-br from-surface-100 via-surface-200 to-surface-300 dark:from-surface-950 dark:via-surface-900 dark:to-surface-950 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-12 relative overflow-hidden">
        <!-- Decorative background elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-500/10 dark:bg-primary-400/5 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 dark:bg-blue-400/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <div class="w-full max-w-6xl mx-auto relative z-10">
            <div class="bg-surface-0 dark:bg-surface-900 shadow-2xl rounded-2xl overflow-hidden backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90 transition-all duration-300 hover:shadow-3xl">
                <div class="flex flex-col lg:flex-row min-h-[500px] md:min-h-[600px]">
                    <!-- Left side - Illustration (hidden on mobile) -->
                    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-primary-500 to-primary-700 dark:from-primary-600 dark:to-primary-900 p-12 items-center justify-center">
                        <div class="absolute inset-0 bg-[url('/images/login-illustrration.jpg')] bg-cover bg-center opacity-20"></div>
                        <div class="relative z-10 text-white text-center space-y-6">
                            <div class="animate-fade-in">
                                <i class="pi pi-shield text-8xl mb-6 opacity-90"></i>
                            </div>
                            <h2 class="text-4xl font-bold leading-tight animate-slide-up">
                                Secure Access
                            </h2>
                            <p class="text-xl opacity-90 animate-slide-up" style="animation-delay: 0.1s;">
                                Sign in to continue to your dashboard
                            </p>
                            <div class="flex items-center justify-center gap-8 mt-12 animate-slide-up" style="animation-delay: 0.2s;">
                                <div class="text-center">
                                    <div class="text-3xl font-bold">99.9%</div>
                                    <div class="text-sm opacity-75">Uptime</div>
                                </div>
                                <div class="h-12 w-px bg-white/30"></div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold">24/7</div>
                                    <div class="text-sm opacity-75">Support</div>
                                </div>
                                <div class="h-12 w-px bg-white/30"></div>
                                <div class="text-center">
                                    <div class="text-3xl font-bold">100%</div>
                                    <div class="text-sm opacity-75">Secure</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side - Login Form -->
                    <div class="w-full lg:w-1/2 p-6 sm:p-8 md:p-12 flex flex-col justify-center">
                        <!-- Mobile logo and header -->
                        <div class="lg:hidden text-center mb-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 mb-4 animate-bounce-gentle">
                                <img src="/vite.svg" class="h-10" alt="App Logo"/>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-surface-900 dark:text-surface-0 mb-2">
                                Welcome Back
                            </h1>
                            <p class="text-surface-600 dark:text-surface-400">
                                Sign in to your account
                            </p>
                        </div>

                        <!-- Desktop header -->
                        <div class="hidden lg:block text-center mb-10 animate-fade-in">
                            <div class="flex items-center justify-center mb-4">
                                <div class="p-3 rounded-full bg-primary-100 dark:bg-primary-900/30 inline-flex animate-bounce-gentle">
                                    <img src="/vite.svg" class="h-12" alt="App Logo"/>
                                </div>
                            </div>
                            <h1 class="text-surface-900 dark:text-surface-0 text-3xl md:text-4xl font-bold mb-2">
                                Welcome Back
                            </h1>
                            <p class="text-surface-600 dark:text-surface-400 text-sm md:text-base">
                                Enter your credentials to access your account
                            </p>
                        </div>
        
                        <Form class="flex flex-col gap-5 animate-slide-up" @submit="loginAction" :resolver="resolver">
                            <FormField name="username" v-slot="$field">
                                <div class="space-y-2">
                                    <label class="font-semibold text-surface-700 dark:text-surface-300 text-sm" for="username">
                                        <i class="pi pi-user mr-2"></i>Username
                                    </label>
                                    <InputText 
                                        id="username" 
                                        placeholder="Enter your username" 
                                        v-model="formSignIn.username" 
                                        type="text" 
                                        class="w-full transition-all duration-200 hover:border-primary-400 focus:border-primary-500" 
                                        :class="{ 'border-red-500': $field.error }"
                                        fluid
                                    />
                                    <Message v-if="$field.error" class="mt-2" severity="error" size="small" variant="simple">
                                        {{ $field.error?.message }}
                                    </Message>
                                </div>
                            </FormField>
                            
                            <FormField name="password" v-slot="$field">
                                <div class="space-y-2">
                                    <label class="font-semibold text-surface-700 dark:text-surface-300 text-sm" for="password">
                                        <i class="pi pi-lock mr-2"></i>Password
                                    </label>
                                    <Password 
                                        id="password" 
                                        placeholder="Enter your password" 
                                        v-model="formSignIn.password" 
                                        :feedback="false" 
                                        toggleMask 
                                        class="w-full transition-all duration-200"
                                        :class="{ 'border-red-500': $field.error }"
                                        :pt="{
                                            input: { class: 'w-full' }
                                        }"
                                        fluid 
                                    />
                                    <Message v-if="$field.error" class="mt-2" severity="error" size="small" variant="simple">
                                        {{ $field.error?.message }}
                                    </Message>
                                </div>
                            </FormField>
        
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
                                <div class="flex items-center">
                                    <Checkbox 
                                        id="remember" 
                                        name="remember" 
                                        v-model="formSignIn.remember" 
                                        :binary="true" 
                                        class="mr-2" 
                                    />
                                    <label for="remember" class="text-sm text-surface-600 dark:text-surface-400 cursor-pointer select-none">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                                    Forgot password?
                                </a>
                            </div>

                            <Button 
                                type="submit" 
                                label="Sign In" 
                                icon="pi pi-sign-in" 
                                class="w-full mt-2 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]" 
                                :loading="loading"
                                size="large"
                            />
                        </Form>

                        <!-- Additional links -->
                        <div class="mt-8 text-center text-sm text-surface-600 dark:text-surface-400">
                            Don't have an account? 
                            <a href="#" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium ml-1 transition-colors">
                                Sign up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-surface-900 dark:bg-surface-950 text-surface-300 dark:text-surface-400 text-center py-4 text-xs sm:text-sm border-t border-surface-700 dark:border-surface-800">
        <div class="container mx-auto px-4">
            <p>© {{ currentYear }} Your Company. All rights reserved.</p>
        </div>
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
