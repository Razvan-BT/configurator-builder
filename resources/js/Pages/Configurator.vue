<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>
<script>

export default {
    props: {
        ID: String,
        default: []
    },
    data() {
        return {
            
        }
    },
    mounted() {
        console.log("Init configurator ID: ", this.ID);
    }
}
</script>

<template>
    <Head title="Setup"/>
    <AuthenticatedLayout>
        
    </AuthenticatedLayout>
</template>