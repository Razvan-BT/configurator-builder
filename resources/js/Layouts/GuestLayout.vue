<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-auto fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
    <footer>
        <div class="d-flex justify-center pb-2">
            <p class="text-sm text-gray-500">
                @ AIT Artificial Intelligence Tailoring - {{ new Date().getFullYear() }} 
            </p>
        </div>
    </footer>
</template>
