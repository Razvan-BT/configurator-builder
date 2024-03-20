<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SuccesButton from '@/Components/SuccesButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimarButton from '@/Components/PrimarButton.vue';
import axios from 'axios';
</script>

<script>
// import { ProductService } from '@/ProductsServices';
import { usePage } from '@inertiajs/vue3';
export default {
    props: {
        configurators: Array,
    },
    data() {
        return {
            products: [
                {
                    category: "Accessories",
                    code: "f230fh0g3",
                    description: "Product Description",
                    id: "1000",
                    image: "bamboo-watch.jpg",
                    inventoryStatus: "INSTOCK",
                    price: 65,
                    quantity: 24,
                    rating: 5,
                }
            ],
            visible: false,
            configurator_name: '',
            configurator_details: '',
            configurator_shopify: '',
            searchedElements: []
        };
    },
    mounted() {
        // ProductService.getProductsMini().then((data) => (this.products = data));

        console.log(this.configurators);
    },
    methods: {
        async sendData() {

            if (this.configurator_name != '') {
                let data = {
                    templateName: this.configurator_name,
                    templateDetails: this.configurator_details,
                    addedBy: usePage().props.auth.user.name,
                    shopifyId: this.configurator_shopify
                }
                let response = await this.axiosAPI('/create-configurator', data);

                this.configurator_name = '';
                this.configurator_details = '';
                this.configurator_shopify = '';
                this.visible = false;

                // console.log(response.data.data.configurator_id)
                if (response.status === 200) {
                    let details = 'Configurator ' + response.data.data.configurator_id + ' was added';
                    this.$toast.add({ severity: 'info', summary: 'Info Message', detail: details, life: 3000 });

                    setTimeout(() => {
                        location.href = "/configurator/" + response.data.data.configurator_id;
                    }, 4000);
                }
            }

        },

        async searchElement(e) {
            this.searchedElements = [];
            let data = {
                search: e.target.value,
            }
            let response = await this.axiosAPI('/search-configurator', data);

            if(response.status == 200) {
                this.searchedElements = response.data.searched;
            }
        },

        editConfigurator(n, i) {
            console.log(n, i);
        },

        routeToConf(id) {
            let routed = route('configurator', { id_c: id });
            window.location.href = routed;
        },

        async duplicateConfigurator(id, title, details = "-") {
            let response = await this.axiosAPI('/duplicate-configurator', {
                id: id,
                title: title,
                details: details,
                by: usePage().props.auth.user.name
            });

            if (response.status === 200) {
                switch (response.data.status) {
                    case 0: {
                        this.$toast.add({ severity: 'error', summary: 'Info Message', detail: response.data.message, life: 3000 });
                        break;
                    }
                    case 1: { // created
                        this.$toast.add({ severity: 'info', summary: 'Info Message', detail: response.data.message, life: 3000 });

                        setTimeout(() => {
                            location.href = "/configurator/" + response.data.configurator;
                        }, 4000);

                        break;
                    }
                }
            }
        },

        async deleteConf(id) {
            let response = await this.axiosAPI('/delete-configurator', {
                id: id,
                by: usePage().props.auth.user.name
            });

            if (response.status == 200) {
                this.$toast.add({ severity: 'error', summary: 'Info Message', detail: response.data.status, life: 3000 });
            }

            this.configurators.filter((value, index) => {
                if (value.configurator_id == id) {
                    this.configurators.splice(index, 1);
                }
            });
        },

        async axiosAPI(url, data) {
            let dataResponse;
            await axios.post(url, data).then(function (response) {
                dataResponse = response;
            }).catch(function (error) {
                console.log("[Error from API]: ", error);
            });
            return dataResponse;
        }
    }
};
</script>


<template>

    <Head title="Main" />
    <AuthenticatedLayout>
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-3 px-2 sm:px-6 lg:px-8">
                <div class="flex flex-wrap align-items-center justify-content-start">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configurators</h2>
                </div>
            </div>
        </header>
        <Toast />


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- tabel optiuni -->
                <div class="flex align-items-center justify-content-around">
                    <div class="p-3 bg-slate-100 rounded shadow w-3/4">
                        <TextInput class="w-full" placeholder="Search an configurator" @change="searchElement($event)"/>
                        <div v-if="searchedElements?.length" class="bg-white rounded p-3">
                            <ul>
                                <li v-for="(value) in searchedElements" class="p-3 border-bottom text-black font-italic"><i class="pi pi-file-o" style="font-size: 1rem"></i> <a :href="`/configurator/${value.configurator_id}`">{{ value.configurator_title }} - {{ value.configurator_id }}</a></li>
                            </ul>
                        </div>
                    </div>

                    <SuccesButton @click.stop="visible = true">
                        <span class="px-1">Create new</span>
                        <i class="pi pi-plus" style="font-size: 1rem"></i>
                    </SuccesButton>
                </div>
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="">

                        <table class="table">
                            <thead>
                                <tr>
                                    <!-- <th scope="col">Image</th> -->
                                    <th scope="col">Title</th>
                                    <!-- <th scope="col">Description</th> -->
                                    <th scope="col">ID</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(items) in configurators">
                                    <!-- <th scope="row">
                                            <img :src="`${items.image}`" :alt="items.image" class="w-6rem shadow-2 border-round" />
                                        </th> -->
                                    <td>{{ items.configurator_title }}</td>
                                    <!-- <td>{{ items.configurator_detail }}</td> -->
                                    <td data-toggle="tooltip" data-placement="bottom"
                                        :title="`${items.configurator_title}`">{{
                        items.configurator_id }}</td>
                                    <td>
                                        <div class="d-flex flex-center">
                                            <div class="p-2"><Button label="Edit" icon="pi pi-spin pi-cog"
                                                    @click="routeToConf(items.configurator_id)" />
                                            </div>
                                            <div class="p-2"><Button label="Clone" icon="pi pi-clone"
                                                    @click="duplicateConfigurator(items.configurator_id, items.configurator_title, items.configurator_detail)" />
                                            </div>
                                            <div class="p-2"><Button label="Delete" icon="pi pi-trash"
                                                    @click="deleteConf(items.configurator_id)" /></div>
                                            <!-- <div class="p-2"><Button label="Clone" icon="pi pi-clone" /></div> -->
                                        </div>




                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </div>
            </div>
        </div>
        <Dialog v-model:visible="visible" modal header="Create new" :style="{ width: '40vw' }">
            <form @submit.prevent="submit">
                <div class="p-3">
                    <InputLabel for="configurator_name" value="Configurator Title" />

                    <TextInput v-model="configurator_name" type="text" class="mt-1 block w-full" autofocus
                        autocomplete="configurator_name" />

                </div>

                <div class="p-3">
                    <InputLabel for="configurator_name" value="Configurator Details" />

                    <TextInput v-model="configurator_details" type="text" class="mt-1 block w-full" autofocus
                        autocomplete="configurator_details" />

                </div>

                <div class="p-3">

                    <h2 class="text-lg font-medium text-gray-900">Shopify</h2>

                    <p class="mt-1 mb-1 text-sm text-gray-600">
                        Import data from shopify using only a valid configurator ID.
                    </p>

                    <InputLabel for="configurator_shopify" value="Shopify configurator ID" />

                    <TextInput v-model="configurator_shopify" type="text" class="mt-1 block w-full" autofocus
                        autocomplete="configurator_shopify" />

                </div>

                <div class="flex items-center justify-end mt-4">
                    <PrimaryButton @click="sendData" class="ml-4">
                        add
                    </PrimaryButton>
                </div>
            </form>

        </Dialog>

    </AuthenticatedLayout>
</template>
