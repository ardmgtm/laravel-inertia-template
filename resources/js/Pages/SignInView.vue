<template>
    <Head title="Sign In" />
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
        
                    <div class="flex flex-col gap-8">
                        <FloatLabel>
                            <label for="username">Username</label>
                            <InputText id="username" v-model="formSignIn.username" type="text" class="w-full" />
                        </FloatLabel>
                        
                        <FloatLabel>
                            <label for="password">Password</label>
                            <InputGroup>
                                <InputText id="password" v-model="formSignIn.password" :type="showPassword ? 'text' : 'password'" class="w-full" />
                                <InputGroupAddon>
                                    <Button :icon="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"  severity="secondary" variant="text" @click="togglePassword" />
                                </InputGroupAddon>
                            </InputGroup>
                        </FloatLabel>
        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <Checkbox id="rememberme" v-model="formSignIn.rememeber_me" :binary="true" class="mr-2" />
                                <label for="rememberme">Remember me</label>
                            </div>
                        </div>
                        <Button label="Sign In" icon="pi pi-send" class="w-full"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-gray-900 text-white text-center py-4 fixed bottom-0 w-full text-sm">
        © {{ currentYear }} Your Company. All rights reserved.
    </footer>
</template>
<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { FloatLabel } from 'primevue';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import { ref } from 'vue';

const currentYear = new Date().getFullYear();
const formSignIn = useForm({
    username : null,
    password : null,
    rememeber_me : false,
})

const showPassword = ref(false);
function togglePassword() {
    showPassword.value = !showPassword.value;
}

</script>
