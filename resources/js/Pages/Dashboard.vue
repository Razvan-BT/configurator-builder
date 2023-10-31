<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
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
        };
    },
    mounted() {
        // ProductService.getProductsMini().then((data) => (this.products = data));

        console.log(this.configurators);
    },
    methods: {
        async sendData() {
            if(this.configurator_name != '') {
                let data = {
                    templateName: this.configurator_name
                }
                let response = await this.axiosAPI('/create-configurator', data);

                this.configurator_name = '';
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
            }). catch(function(error) {
                console.log("[Error from API]: ", error);
            });
            return dataResponse;
        }
    }
};
</script>


<template>
    <Head title="Main"/>
    <AuthenticatedLayout>
            <Toast />

        <div class="card px-8">
            <DataTable :value="configurators">
                <template #header>
                    <div class="flex flex-wrap align-items-center justify-content-between gap-2">
                        <span class="text-xl text-900 font-bold">Configurators</span>
                        <Button icon="pi pi-plus" rounded raised @click.stop="visible = true" />
                    </div>
                </template>
                <Column field="configurator_title" header="Name"></Column>
                <Column header="Image">
                    <template #body="slotProps">
                        <img :src="`${slotProps.data.image}`" :alt="slotProps.data.image" class="w-6rem shadow-2 border-round" />
                    </template>
                </Column>
                <Column field="configurator_id" header="ID"></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="card flex gap-3">
                            <div class="card flex flex-wrap gap-3 justify-content-center">
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
            </DataTable>

        <Dialog v-model:visible="visible" modal header="Create new" :style="{ width: '40vw' }">
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="configurator_name" value="Configurator Title" />

                    <TextInput
                        v-model="configurator_name"
                        type="text"
                        class="mt-1 block w-full"
                        autofocus
                        autocomplete="configurator_name"
                    />

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
