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
    methods: {
        convertData(data) {
            // Parse the timestamp string
            const timestamp = new Date(data);

            // Options for formatting the date
            const options = {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "numeric",
            minute: "numeric",
            second: "numeric",
            timeZone: "UTC",
            timeZoneName: "short"
            };

            // Format the date according to the options
            const formattedDate = timestamp.toLocaleString("en-US", options);
            return formattedDate;
        }
    }
};
</script>


<template>
    <Head title="Logs" />
    <AuthenticatedLayout>
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-3 px-2 sm:px-6 lg:px-8">
                <div class="flex flex-wrap align-items-center justify-content-start">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Logs</h2>
                </div>
            </div>
        </header>
        <Toast />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" style="text-align: center;">By</th>
                            <th scope="col" style="text-align: center;">At</th>
                            <th scope="col" style="text-align: center;">Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(items) in logs" style="text-align: center;">
                            <td>{{ items.by_ }}</td>
                            <td>{{ convertData(items.created_at) }}</td>
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
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
a {
    color: rgb(115, 115, 223);
}
</style>