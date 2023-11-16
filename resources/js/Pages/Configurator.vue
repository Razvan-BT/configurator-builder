<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimarButton from '@/Components/PrimarButton.vue';
import SuccesButton from '@/Components/SuccesButton.vue';
import EditButton from '@/Components/EditButton.vue';
import { Head } from '@inertiajs/vue3';

import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

</script>
<script>
import { usePage } from '@inertiajs/vue3';
export default {
    props: {
        ID: String,
        title: String,
    },
    data() {
        return {
            extra_class_step: "",
            title_product: "",
            sku_prefix: "",
            productsType: [],
            product: {},
            addNewProductButton: false,

            selectCurrentProductIndex: -1,
            createNewCategory: false,

            extra_class_stepCategory: "",
            title_productCategory: "",

            stockTempData: [],
        }
    },
    methods: {
        createTypeOfProduct() {
            console.log("createNewProduct clicked x3 ");
            this.addNewProductButton = true;
            // this.productsType.push("New Product");

            // this.data = {
            //     panels: [],

            // }
        },
        cancelCreateNewProduct() {
            console.log("cancelCreateNewProduct clicked x3");
            this.addNewProductButton = false;
            this.extra_class_step = "";
            this.title_product = "";
            this.sku_prefix = "";

        },
        createNewProduct() {
            // creez API to SQL dupa generez in JSON
            this.productsType.push(this.title_product);

            this.createToast({
                type: 'success',
                title: 'Info message',
                details: this.title_product.toUpperCase() + " product added succesfully!"
            });

            // create new product
            // @ start JSON
            let object = {};
            object = {
                id: this.makeid(15),
                title: this.title_product,
                categories: [],
                extraClassName: this.extra_class_step,
                logic: {},
                skuPrefix: this.sku_prefix,
            }

            this.stockTempData.push(object);

            this.product = {
                panels: this.stockTempData
            }
            // console.log(this.product);


            this.addNewProductButton = false;
            this.extra_class_step = "";
            this.title_product = "";
            this.sku_prefix = "";
        },

        initGlobalObject() {
            console.log("initGlobalObject init");
        },

        createNewOptionForProduct() {
            console.log("createNewOptionForProduct init");
            this.createNewCategory = true;
        },

        cancelCreateNewCategory() {
            console.log("cancelCreateNewCategory clicked x3");
            this.createNewCategory = false;
            this.extra_class_stepCategory = "";
            this.title_productCategory = "";
        },

        // Cand se creeaza o noua categorie de produs
        createNewCategoryPanel() {
            if(this.selectedProduct) {
                this.selectedProduct.categories.push({
                    title: this.title_productCategory,
                    extraClassName: this.extra_class_stepCategory,
                    panelId: this.selectedProduct?.length ? this.selectedProduct : "",
                    logic: {
                        rules: [],
                        action: 'show'
                    }
                });
            }

            this.createToast({
                type: 'success',
                title: 'Info message',
                details: this.title_productCategory.toUpperCase() + " category added succesfully!"
            });

            this.cancelCreateNewCategory();
        },

        getCurrentProduct(getItem, index) {
            console.log("getCurrentProduct", getItem);

            this.selectCurrentProductIndex = index;
        },

        createToast(details) {
            this.$toast.add({ severity: details.type, summary: details.title, detail: details.details, life: 3000 });
        },

        makeid(length) {
            let result = '';
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            let counter = 0;
            while (counter < length) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
                counter += 1;
            }
            return result;
        },

    },

    watch: {
        product: {
            handler(data) {
                this.initGlobalObject();
                console.log("Object base changed", data);

            }
        }
    },

    mounted() {
        console.log("Init configurator ID: ", this.ID, usePage().props.auth.user.name, this.title);
    },

    computed: {
        selectedProduct() {
            if (this.product.panels.length) {
                return this.product.panels[this.selectCurrentProductIndex != -1 ? this.selectCurrentProductIndex : 0];
            }
        },
        selectedProductCategories() {
            if (this.product.panels.length) {
                return this.product.panels[this.selectCurrentProductIndex != -1 ? this.selectCurrentProductIndex : 0].categories;
            }
        }
    }
}
</script>

<template>
    <Head title="Setup" />
    <AuthenticatedLayout>
        <Toast />
        <div class="container">
            <div class="container-sm">
                <p class="p-3 h2">{{ this.title?.length ? this.title : "No Title" }}</p>
            </div>

            <!-- Butoane selectie  -->
            <div class="container-sm d-flex flex-wrap">
                <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                    <div v-for="(items, index) in this.product.panels" class="btn-group me-2 mt-2" role="group">
                        <PrimarButton @click="getCurrentProduct(items, index)">{{ items.title }}</PrimarButton>
                    </div>
                    <div class="btn-group" role="group">
                        <SuccesButton @click="createTypeOfProduct">
                            <i class="pi pi-plus" style="font-size: 1rem"></i>
                        </SuccesButton>
                    </div>
                </div>
            </div>

            <!-- Container Elemente -->
            <div class="mt-5">
                <div class="container-sm d-flex p-2 border-1 border-secondary">
                    <div class="p-2 flex-grow-1">
                        <p class="p-3 h4">{{ product.panels?.length ? selectedProduct.title : 'No title' }}</p>
                    </div>
                    <div class="pt-4 px-1">
                        <i class="p-1 pi pi-file-edit" style="font-size: 1rem"></i>
                    </div>
                    <div class="pt-4">
                        <i class="p-1 pi pi-clone" style="font-size: 1rem"></i>
                    </div>
                    <div class="pt-4">
                        <i class="p-1 pi pi-trash" style="font-size: 1rem"></i>
                    </div>
                </div>
            </div>

            <!-- Container Content -->
            <div class="mt-5 mb-5">
                <div class="container-sm p-2 border-1 border-secondary">
                    <div v-if="product.panels?.length" class="mt-2 border-bottom border-bottom">
                        <div v-for="(items) in selectedProductCategories" class="p-1">
                            <!-- div titlu + btn -->
                            <div class="d-flex mt-1">
                                <div class="p-1 flex-grow-1">
                                    <p class="px-3 h5"><i class="pi pi-eye-slash"></i> {{ items.title }}</p>
                                </div>
                                <div class="p-1">
                                    <i class="p-1 pi pi-file-edit" style="font-size: 1rem"></i>
                                </div>
                                <div class="p-1">
                                    <i class="p-1 pi pi-copy" style="font-size: 1rem"></i>
                                </div>
                                <div class="p-1">
                                    <i class="p-1 pi pi-arrow-right-arrow-left" style="font-size: 1rem"></i>
                                </div>
                                <div class="p-1">
                                    <i class="p-1 pi pi-clone" style="font-size: 1rem"></i>
                                </div>
                                <div class="p-1">
                                    <i class="p-1 pi pi-trash" style="font-size: 1rem"></i>
                                </div>
                            </div>
                            <!-- div poze grid -->
                            <div class="d-flex align-content-start flex-wrap">
                                <div class="p-4 images-layout">
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                    <div class="p-1">
                                        <img style="width: 150px; height: 150px;"
                                            src="https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-4crxSURXBmaNc7TrHlwxhA19.png"
                                            alt="">
                                    </div>
                                </div>
                            </div>
                            <!-- edit attributes -->

                            <div class="d-flex p-4">
                                <EditButton>
                                    Edit attributes
                                </EditButton>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 d-flex justify-center">
                        <SuccesButton @click="createNewOptionForProduct">
                            Add new custom option
                        </SuccesButton>
                    </div>
                </div>
            </div>
        </div>


        <!-- modal add new product -->

        <Dialog v-model:visible="addNewProductButton" modal header="Add new product" :style="{ width: '40vw' }">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane"
                        type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane"
                        type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Logic</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab"
                    tabindex="0">
                    <form @submit.prevent="submit">
                        <div class="p-3">
                            <InputLabel for="title_product" value="Title" />

                            <TextInput v-model="title_product" type="text" class="mt-1 block w-full" autofocus
                                autocomplete="" />

                        </div>

                        <div class="p-3">
                            <InputLabel for="sku_prefix" value="Sku Prefix" />

                            <TextInput v-model="sku_prefix" type="text" class="mt-1 block w-full" autofocus
                                autocomplete="" />

                        </div>

                        <div class="p-3">
                            <InputLabel for="extra_class_step" value="Extra class new step" />

                            <TextInput v-model="extra_class_step" type="text" class="mt-1 block w-full" autofocus
                                autocomplete="" />

                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <SuccesButton @click="createNewProduct" class="ml-4">
                                Apply
                            </SuccesButton>
                            <PrimaryButton @click="cancelCreateNewProduct" class="ml-4">
                                Cancel
                            </PrimaryButton>
                        </div>
                    </form>

                </div>
                <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                    ...</div>
            </div>

            <!-- logic -->
            <!-- Apply & Cancel -->
        </Dialog>

        <!-- modal add new category -->

        <Dialog v-model:visible="createNewCategory" modal header="Add new category option" :style="{ width: '40vw' }">

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane"
                        type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane"
                        type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Logic</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab"
                    tabindex="0">
                    <form @submit.prevent="submit">
                        <div class="p-3">
                            <InputLabel for="title_productCategory" value="Title" />

                            <TextInput v-model="title_productCategory" type="text" class="mt-1 block w-full" autofocus
                                autocomplete="" />

                        </div>

                        <div class="p-3">
                            <InputLabel for="extra_class_stepCategory" value="Extra class" />

                            <TextInput v-model="extra_class_stepCategory" type="text" class="mt-1 block w-full" autofocus
                                autocomplete="" />

                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <SuccesButton @click="createNewCategoryPanel" class="ml-4">
                                Apply
                            </SuccesButton>
                            <PrimaryButton @click="cancelCreateNewCategory" class="ml-4">
                                Cancel
                            </PrimaryButton>
                        </div>
                    </form>

                </div>
                <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                    ...</div>
            </div>

            <!-- logic -->
            <!-- Apply & Cancel -->
        </Dialog>
    </AuthenticatedLayout>
</template>


<style>
.images-layout {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
}
</style>