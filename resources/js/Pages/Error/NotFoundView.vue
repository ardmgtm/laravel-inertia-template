<template>
  <Head title="Not Found"/>
  <div class="w-full h-screen flex items-center justify-center flex-col m-auto gap-4 bg-surface-50 p-8">
    <img src="/images/404.webp" style="max-height:190px" alt="404"/>
    <h1 class="font-bold text-4xl text-center">404 - Page Not Found</h1>
    <p class="text-xl text-center">Sorry, the page you are looking for does not exist.</p>
    <Button as="a" href="/" label="Back to home" icon="pi pi-home"/>
  </div>
</template>

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
</script>