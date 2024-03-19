<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<script>
export default {
    props: {
        logs: Array,
    },
    data() {
        return {};
    },
    mounted() {
        // ProductService.getProductsMini().then((data) => (this.products = data));

        console.log(this.logs);
    },
    methods: {}
};
</script>


<template>
    <Head title="Logs" />
    <AuthenticatedLayout>
        <Toast />

        <div class="card px-8">

            <!-- tabel optiuni -->
            <div class="flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                <span class="text-xl text-900 font-bold">Logs</span>
            </div>
            <div class="d-flex justify-content-center">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">By</th>
                            <th scope="col">Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(items) in logs">
                            <td>{{ items.by_ }}</td>
                            <td v-if="items.action == 'New'">
                                Generated new configurator <a :href="`/configurator/${items.configurator}`">{{ items.configurator }}</a>.
                            </td>
                            <td v-if="items.action == 'Shopify'">
                                Imported <a :href="`/configurator/${items.configurator}`">{{ items.configurator }}</a> from Shopify.
                            </td>
                            <td v-if="items.action == 'Delete'">
                                <a href="#">{{ items.configurator }}</a> was deleted.
                            </td>
                            <td v-if="items.action == 'Edit'">
                                <a :href="`/configurator/${items.configurator}`">{{ items.configurator }}</a> was edited.
                            </td>
                            <td v-if="items.action.startsWith('Duplicate')">
                                <a :href="`/configurator/${items.configurator}`">{{ items.configurator }}</a> was duplicated after configurator {{ items.action.replace('Duplicate', '').toLowerCase() }}.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style>
a {
    color: rgb(115, 115, 223);
}
</style>