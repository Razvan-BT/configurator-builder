<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimarButton from '@/Components/PrimarButton.vue';
import axios from 'axios';
</script>

<script>
// import { ProductService } from '@/ProductsServices';

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
                }
                let response = await this.axiosAPI('/create-configurator', data);

                this.configurator_name = '';
                this.configurator_details = '';
                this.visible = false;

                // console.log(response.data.data.configurator_id)
                let details = 'Configurator ' + response.data.data.configurator_id + ' was added';
                this.$toast.add({ severity: 'info', summary: 'Info Message', detail: details, life: 3000 });

                setTimeout(() => {
                    location.href = "/configurator/" + response.data.data.configurator_id;
                }, 4000);
            }
        },
        editConfigurator(n, i) {
            console.log(n, i);
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
        <Toast />

        <div class="card px-8">
            <!-- <DataTable :value="configurators">
                <Column header="Image">
                    <template #body="slotProps">
                        <img :src="`${slotProps.data.image}`" :alt="slotProps.data.image" class="w-6rem shadow-2 border-round" />
                    </template>
                </Column>
                <template #header>
                    <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                        <span class="text-xl text-900 font-bold">Configurators</span>
                        <PrimarButton @click.stop="visible = true">
                            <i class="pi pi-plus" style="font-size: 1rem"></i>
                        </PrimarButton>
                    </div>
                </template>
                <Column field="configurator_title" header="Name"></Column>
                <Column field="configurator_detail" header="Details"></Column>
                <Column field="configurator_id" header="ID"></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="card flex gap-3">
                            <div class="card flex flex-wrap justify-content-between">
                                <Button label="Edit" icon="pi pi-spin pi-cog"
                                @click="route('testt')"
                                />
                                <Button label="Delete" icon="pi pi-trash" 
                            
                                />
                            </div>

                        </div>
                    </template>
                </Column>
                <template #footer> In total there are {{ configurators ? configurators.length : 0 }} products. </template>
            </DataTable> -->

            <!-- tabel optiuni -->
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Configurators</th>
                        <th scope="col">Name</th>
                        <th scope="col">ID</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(items) in configurators">
                        <th scope="row">
                            <img :src="`${items.image}`" :alt="items.image" class="w-6rem shadow-2 border-round" />
                        </th>
                        <td>{{ items.configurator_title }}</td>
                        <td>{{ items.configurator_detail }}</td>
                        <td>{{ items.configurator_id }}</td>
                        <td>
                            <div class="d-flex flex-center">
                                <div class="p-2"><Button label="Edit" icon="pi pi-spin pi-cog" @click="route('/configurator/' + items.configurator_id)" />
                                </div>
                                <div class="p-2"><Button label="Delete" icon="pi pi-trash" /></div>
                                <div class="p-2"><Button label="Clone" icon="pi pi-clone" /></div>
                            </div>




                        </td>
                    </tr>
                </tbody>
            </table>

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
                    <div class="flex items-center justify-end mt-4">
                        <PrimaryButton @click="sendData" class="ml-4">
                            add
                        </PrimaryButton>
                    </div>
                </form>

            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>
