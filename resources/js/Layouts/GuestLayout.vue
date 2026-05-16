<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <Toast />
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full bg-white px-6 py-8 shadow-2xl shadow-orange-950/10 sm:max-w-md sm:rounded-3xl border border-orange-50"
        >
            <slot />
        </div>
    </div>
</template>
