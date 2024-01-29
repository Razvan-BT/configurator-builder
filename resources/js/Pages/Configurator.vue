<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimarButton from '@/Components/PrimarButton.vue';
import SuccesButton from '@/Components/SuccesButton.vue';
import EditButton from '@/Components/EditButton.vue';
import { Head } from '@inertiajs/vue3';

import WarningButton from '@/Components/WarningButton.vue';
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
            imagePreview: null,
            isOverlayVisible: false,
            extra_class_step: "",
            title_product: "",
            sku_prefix: "",
            productsType: [],
            product: {
                settings: {
                    defaultOptions: {}
                },
                data: {}
            },
            addNewProductButton: false,

            selectCurrentProductCategoryIndex: -1,
            selectCurrentProductIndex: -1,
            createNewCategory: false,

            extra_class_stepCategory: "",
            title_productCategory: "",
            typeCategory: "img", // default

            createNewCategoryOption: false,
            newOptionRequest: false,

            stockTempData: [],

            optionSKU: "",
            optionLabel: "",

            isLoading: true,
            loaderColor: '#6d5cae',
            selectedFile: '',
            path_image: '',

            editOption: false,
            editOptionID: -1,

            editPanel: false,

            deleteElementVar: false,
            deleteElementCat: '',
            deleteElementId: '',

            panelLogic: 'Select Step',
            optionLogic: 'Select Custom Option',
            ruleLogic: {},

            rulesPanel: {
                logic: {
                    rules: []
                },
                action: "show"
            },
            rulesPanelPanels: [],
            selectedValues: {},
            rulesPanelCategories: [],
            selectedCategories: {},

            rulesCategories: [],
            rulesOptions: [],

            panelLogicOptions: [],
            optionLogicOptionsCategories: [],
            ruleLogicOptions: [],

        }
    },
    methods: {
        simulateLoading() {
            setTimeout(() => {
                // simulare loading + check daca data exist
                // Atunci incarc ce am in JSON 
                let response = axios.get("/get-product/" + this.ID);
                response.then((result) => {
                    if ('error' in result.data) {
                        console.warn("Configurator data ID doesn't exist");
                        this.isLoading = false;
                    } else {

                        this.initGlobalObject(result);

                        if (this.product.data.panels.length) {
                            // Populate logic 

                            this.repopulateLogic();
                        }
                    }
                }).catch((error) => {
                    console.log(error);
                    this.isLoading = false;
                });
            }, 3000); // Simulating a 3-second loading time
        },

        repopulateLogic() {
            this.panelLogicOptions = []; // reset
            this.product.data.panels.filter((value, index) => {
                // panels
                let obj = {
                    id: value.id,
                    title: value.title,
                    categories: value.categories
                };

                this.panelLogicOptions.push(obj);

            });
        },

        createNewRule(where) {
            // creez o noula regula in 
            /*
                panel,
                categorie,
                optiune
            */

            if (where == 'panel') {
                // preiau paneluriile pentru fiecare index ID
                this.rulesPanelPanels = [];
                let panels = {};
                this.panelLogicOptions.filter((value, index) => {
                    panels = {
                        panels: this.product.data.panels,
                    }
                });
                this.rulesPanelPanels.push(panels);
                let obj = {

                    panel: "",
                    category: "",
                    option: "oneof",
                    operator: "||",
                    layer: "",
                    active: "",
                    logicRuleLabel: "",
                    options: []

                }
                this.rulesPanel.logic.action = "show";
                this.rulesPanel.logic.rules.push(obj);

                // this.selectedProduct.logic = this.rulesPanel.logic;

                let returnIndex = this.rulesPanel.logic.rules.length;
                if (returnIndex == 0) this.selectedValues[0] = 'Select Step';
                else {
                    this.selectedValues[returnIndex] = 'Select Step';
                    this.selectedCategories[returnIndex] = 'Select Custom Option';
                    this.ruleLogic[returnIndex] = 'anychoice';
                }
            }
        },

        // logic for panel
        getCategoriesAfterPanel(panel) {
            let obj = [];
            this.product.data.panels.filter((value, index) => {
                if (value.id == panel) {
                    obj.push(value.categories)
                }
            })
            return obj[0];
        },

        getOptionsAfterPanelAndCategory(panel, caty) {
            let obj = [];
            this.product.data.panels.filter((value, index) => {
                if (value.id == panel) {
                    value.categories.filter((cat, idx) => {
                        if (cat.id == caty) obj.push(cat.options);
                    })
                }
            })
            return obj[0];
        },

        deleteCurrentLogic(index) {
            delete this.selectedValues[index];
            delete this.selectedCategories[index];
            delete this.ruleLogic[index];

            console.log("Delete ITEM: ", this.rulesPanel.logic.rules.splice(index, 1));
            // this.rulesPanel.splice(index, 1);

            this.rulesPanel.logic.rules.forEach((value, idx) => {
                this.selectedValues[index] = value.panel;
                this.selectedCategories[index] = value.category;
                this.ruleLogic[index] = 'anychoice';
            });
        },

        // logic for panel END
        async saveProduct() {
            // console.log("TEEST", this.selectedProduct.logic)
            this.isOverlayVisible = true;

            let data = {
                configuratorId: this.ID,
                data: this.product,
            }
            let response = await this.axiosAPI('/saveProduct', data);
            console.log("PRODUCT", response);

            this.createToast({
                type: 'success',
                title: 'Info message',
                details: response.data.message
            });

            if (response?.data.message) {
                this.isOverlayVisible = false;
            }
        },

        editCurrentOption(path) {
            // cand voi face edit la o optiune din edit choise 
            /**
             * Doar schimb sku
             * label si poza + reset valori la normal
             */
            if (!this.editOption) return;
            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].sku = this.optionSKU;
            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].option.data.label = this.optionLabel;
            if (path?.length) this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].option.data.value = '/storage/' + path;
            console.log("Razvan debug 22", this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID]);

            this.editOption = false;
            this.editOptionID = -1;
            this.optionSKU = '';
            this.optionLabel = '';
            this.selectedFile = null;
            this.imagePreview = '';
            this.newOptionRequest = false;
            this.createNewCategoryOption = false;
            if (this.selectedFile) this.selectedFile = '';
            this.createToast({
                type: 'info',
                title: 'Info message',
                details: "Option edited successfull!"
            });
        },

        editChosedOption(option, index) {
            // repopulez input dupa save --> doar optiunea selectata
            this.newOptionRequest = true;
            this.editOption = true;
            this.editOptionID = index;
            this.optionSKU = option.sku;
            this.imagePreview = option.option.data.value;
            this.optionLabel = option.option.data.label;
            console.log("editChosedOption", option.sku)
        },

        handleFileSelect(event) {
            this.selectedFile = event.target.files[0];
            this.displayImage(this.selectedFile);
        },
        handleDragOver(event) {
            event.dataTransfer.dropEffect = "copy";
        },
        handleDragLeave() {
            // Handle drag leave styling if needed
        },
        handleDrop(event) {
            event.preventDefault();
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.displayImage(files[0]);
            }
        },
        displayImage(file) {
            if (file) {
                const reader = new FileReader();

                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };

                reader.readAsDataURL(file);
            }
        },

        async axiosAPI(url, data) {
            let dataResponse;
            await axios.post(url, data).then(function (response) {
                dataResponse = response;
            }).catch(function (error) {
                console.log("[Error from API]: ", error);
            });
            return dataResponse;
        },

        cancelNewOption() {
            this.newOptionRequest = false;
        },

        createTypeOfProduct() {
            console.log("createNewProduct clicked x3 ");
            this.addNewProductButton = true;

        },
        cancelCreateNewProduct() {
            console.log("cancelCreateNewProduct clicked x3");
            if (this.editPanel) this.editPanel = false;
            this.addNewProductButton = false;
            this.extra_class_step = "";
            this.title_product = "";
            this.sku_prefix = "";
            this.selectCurrentProductIndex = -1;
        },

        deleteElement(element, id = null) {
            /**
             * element 1 - panel
             * element 2 - categorie
             * element 3 - optiune
             */

            switch (element) {
                case 'panel': {
                    if (id !== null) return;
                    console.log('Element panel: ', this.selectedProduct);
                    // delete all element with same panel ID 
                    this.deleteElementVar = true;

                    this.deleteElementCat = 'panel';
                    this.deleteElementId = this.selectedProduct;
                    break;
                }
                case 'category': {
                    console.log('Element panel categories: ', id);
                    // delete all element with same panel ID 
                    this.deleteElementVar = true;

                    this.deleteElementCat = 'category';
                    this.deleteElementId = id;
                    break;
                }
                default: {
                    this.createToast({
                        type: 'error',
                        title: 'Error message',
                        details: "Error to delete element check console."
                    });
                    console.error('deleteEmelent are impossible - element not set');
                    break;
                }
            }

        },

        sendConfirmation() {
            switch (this.deleteElementCat) {
                case 'panel': {
                    this.product.data.panels.filter((el, index) => {
                        if (this.deleteElementId.id == el.id && this.selectedProduct.id == el.id) {
                            console.log("Razvan delete panel", el)
                            this.product.data.panels.splice(this.product.data.panels.indexOf(el.id), 1);
                            if (this.product.data.panels?.length > 1) this.selectCurrentProductIndex = 0;
                            else this.selectCurrentProductIndex = 0;
                            this.deleteElementVar = false;
                            this.deleteElementId = '';
                            this.deleteElementCat = '';
                        }
                    });
                    break;
                }
                case 'category': {
                    // delete category
                    this.selectedProductCategories.filter((el, index) => {
                        if (this.deleteElementId.id == el.id) {
                            console.log("Razvan delete category", el)
                            this.selectedProductCategories.splice(this.selectedProductCategories.indexOf(el.id), 1);
                            this.deleteElementVar = false;
                            this.deleteElementId = '';
                            this.deleteElementCat = '';
                        }
                    });
                    break;
                }
            }
        },

        createNewProduct() {
            // creez API to SQL dupa generez in JSON

            if (this.editPanel) {
                this.createToast({
                    type: 'success',
                    title: 'Info message',
                    details: this.title_product.toUpperCase() + " was edited succesfully!"
                });

                this.selectedProduct.title = this.title_product || "";
                this.selectedProduct.skuPrefix = this.sku_prefix || "";
                this.selectedProduct.extraClassName = this.extra_class_step || "";
                this.selectedProduct.logic = this.rulesPanel.logic || {};

                this.rulesPanel.logic = {
                    rules: [],
                    action: 'hide'
                }
                this.addNewProductButton = false;
                this.extra_class_step = "";
                this.title_product = "";
                this.sku_prefix = "";
                this.editPanel = false; // resetez daca a fost edit
                return;
            }

            this.productsType.push(this.title_product);

            this.createToast({
                type: 'success',
                title: 'Info message',
                details: this.title_product.toUpperCase() + " product added succesfully!"
            });

            // create new product
            // @ start JSON
            // if (!this.product.data?.panels) {

            let object = {};
            object = {
                id: this.makeid(15),
                title: this.title_product,
                description: "",
                categories: [],
                extraClassName: this.extra_class_step,
                logic: this.rulesPanel.logic,
                skuPrefix: this.sku_prefix,
                zIndex: 0,
            }

            /**
             * Daca am citit un produs cu date;
             * atunci cand creez o alta categorie --> sa fie adaugata dupa restul
             */
            if (this.product.data?.panels) {
                this.product.data.panels.push(object);

            } else {

                this.stockTempData.push(object);
                this.product.data = {
                    name: this.title,
                    base: {
                        price: 123,
                    },
                    panels: [...this.stockTempData],
                }
            }

            // }
            // console.log(this.product);


            this.addNewProductButton = false;
            this.extra_class_step = "";
            this.title_product = "";
            this.sku_prefix = "";
            this.rulesPanel.logic = {
                rules: [],
                action: 'hide'
            };
        },

        initGlobalObject(data) {
            console.log("initGlobalObject init", data.data.data.panels);
            this.product.data.panels = data.data.data.panels;

            this.isLoading = false;
        },

        initLogic() {
            // reinit logic
            // panel
            this.repopulateLogic();
            this.rulesPanelPanels = [];
            let panels = {};
            this.panelLogicOptions.filter((value, index) => {
                panels = {
                    panels: this.product.data.panels,
                }
            });

            this.rulesPanelPanels.push(panels);
            if (this.rulesPanel.logic.rules?.length) {
                this.rulesPanel.logic.rules.forEach((value, index) => {
                    this.selectedValues[index] = value.panel;
                    this.selectedCategories[index] = value.category;
                    this.ruleLogic[index] = value.option;
                })
            }
        },

        createNewOptionForProduct() {
            if (this.product.data?.panels) {
                console.log("createNewOptionForProduct init");
                this.createNewCategory = true;
            } else {

                this.createToast({
                    type: 'error',
                    title: 'Error message',
                    details: "There is no valid product!"
                });

            }
        },

        cancelCreateNewCategory() {
            console.log("cancelCreateNewCategory clicked x3");
            this.createNewCategory = false;
            this.extra_class_stepCategory = "";
            this.title_productCategory = "";
        },

        // Cand se creeaza o noua categorie de produs
        createNewCategoryPanel() {
            if (this.selectedProduct) {
                this.selectedProduct.categories.push({
                    id: this.makeid(24),
                    zIndex: this.selectedProduct.categories?.length + 1, // order elements
                    type: this.typeCategory?.length ? this.typeCategory : "img",
                    title: this.title_productCategory,
                    extraClassName: this.extra_class_stepCategory,
                    panelId: this.selectedProduct ? this.selectedProduct.id : "",
                    panelID: this.selectedProduct ? this.selectedProduct.id : "",
                    options: [],
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
            
            this.rulesPanel.logic.rules = [];
            this.selectedValues = {};
            this.selectedCategories = {};
            this.ruleLogic = {};
        },

        editChoiseAttribute(getItem, index) {

            this.newOptionRequest = false;
            this.createNewCategoryOption = true;
            this.selectCurrentProductCategoryIndex = index;
            console.log("editChoiseAttributes", this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options);
        },

        generateNewOption() {
            let data = [], status = false;
            if (this.selectedProduct) {
                if (this.selectedFile) {
                    const formData = new FormData();
                    formData.append('image', this.selectedFile);

                    axios.post('/upload-image', formData)
                        .then(response => {
                            this.path_image = response.data?.image_path ? response.data.image_path : '';
                            if (!this.editOption) this.addNewOption(response.data.image_path);
                            else this.editCurrentOption(response.data.image_path);
                        })
                        .catch(error => {
                            console.log(error);
                        });

                } else {

                    if (this.editOption) this.editCurrentOption();
                }

            }
            // resetez valori dupa generare optione
            console.log(this.selectedProduct.categories[this.selectCurrentProductCategoryIndex])
        },

        createToast(details) {
            this.$toast.add({ severity: details.type, summary: details.title, detail: details.details, life: 3000 });
        },

        addNewOption(path) {
            //  adaug dupa response in alta functie deoarece nu imi permite requestul..
            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options.push({
                option: {
                    data: {
                        label: this.optionLabel,
                        inputMaxValue: 999999,
                        inputMinValue: 0,
                        inputStep: 1,
                        valuePreview: null,
                        value: '/storage/' + path,
                        // value: "https://cdn.thecustomproductbuilder.com/45402292382/manufacture-paris-7027336872094-Z43Fm9TPy0MfmgrmgxBl5j7r.png", // VALUE E IMG
                        defaultSelectValue: null,
                        chargePerCharacter: false,
                        inputLengthValue: 100,
                        inputMinLengthValue: 0,
                        useCustomCharacterPrcies: false,
                        countSpaceAsCharacter: false,
                        countSpaceAsCharacterForValidation: true,
                        showCounterOfEnteredCharacters: false,
                    }
                },
                inStock: true,
                sku: this.optionSKU,
                logic: {
                    rules: [],
                    action: "hide"
                },
                id: this.makeid(24),
                inventory: null,
                quantityMultiplier: 1,
                weight: 0,
                showWhenOutOfStock: false,
            });


            this.selectedFile = ''; // elementar daca vreau o sterg
            this.optionSKU = '';
            this.optionLabel = '';
            this.selectedFile = null;
            this.imagePreview = '';
            this.newOptionRequest = false;
            this.createNewCategoryOption = false;
            if (this.selectedFile) this.selectedFile = '';
            this.createToast({
                type: 'info',
                title: 'Info message',
                details: "New option added!"
            });
        },

        editCurrentPanel() {
            console.log("editCurrentPanel xx2")
            if (this.product.data.panels?.length) {
                this.editPanel = true;
                this.addNewProductButton = true;

                this.title_product = this.selectedProduct.title || "";
                this.sku_prefix = this.selectedProduct.skuPrefix || "";
                this.extra_class_step = this.selectedProduct.extraClassName || "";
                // this.rulesPanel.logic = this.selectedProduct.logic || {rules: [], action: 'hide'};

                console.log("editCurrentPanel Razvan selected product", this.selectedProduct, this.rulesPanel.logic);
                
                // repopulez llogic panel cu panel ID selectat.
                if (this.selectedProduct.logic.rules?.length) {

                    let response = axios.get("/get-product/" + this.ID);
                    response.then((result) => {
                        if ('error' in result.data) {
                            console.warn("Configurator data ID doesn't exist");
                        } else {

                            if(result.data.data.panels?.length) result.data.data.panels.forEach((v, i) => {
                                if(v.id == this.selectedProduct.id) {

                                    this.rulesPanel.logic = v.logic;
                                    this.rulesPanel.logic.action = "show";
                                    this.initLogic();
                                }
                            });
                        }
                    }).catch((error) => {
                        console.log(error);
                    });
                }
            }
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
                console.log("Object base changed", this.product);
            }
        },
        createNewCategoryOption: {
            handler(data) {
                // reset values from add option x btn
            }
        },
        addNewProductButton: {
            handler(data) {
                console.log("Click X Btn addNewProductButton", data);
                if (!data) {
                    this.cancelCreateNewProduct();
                }
            }
        },
        selectedValues: {
            deep: true,
            handler(newValues, oldValues) {
                for (let i in this.selectedValues) {

                    this.rulesPanel.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.panel = newValues[i];
                            this.selectedValues[index - 1];
                        }
                    });
                }
            },
        },
        selectedCategories: {
            deep: true,
            handler(newValue, oldValue) {
                for (let i in this.selectedCategories) {

                    console.log("Category changed", newValue[i])
                    this.rulesPanel.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.category = newValue[i];
                            this.selectedCategories[index - 1];
                        }
                    });
                }
            }
        },
        ruleLogic: {
            deep: true,
            handler(newValue, oldValue) {
                for (let i in this.ruleLogic) {

                    console.log("Option rule changed", newValue[i])
                    this.rulesPanel.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.option = newValue[i];
                            this.ruleLogic[index - 1];
                        }
                    });
                }
            }
        },
    },

    mounted() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        // console.log("Init configurator ID: ", this.ID, usePage().props.auth.user.name);
        this.simulateLoading();
    },

    computed: {
        selectedProduct() {
            if (this.product.data.panels.length) {
                return this.product.data.panels[this.selectCurrentProductIndex != -1 ? this.selectCurrentProductIndex : 0];
            }
        },
        selectedProductCategories() {
            if (this.product.data.panels.length) {
                return this.product.data.panels[this.selectCurrentProductIndex != -1 ? this.selectCurrentProductIndex : 0].categories;
            }
        }
    },

}
</script>

<template>
    <Head title="Setup" />
    <AuthenticatedLayout>
        <div v-if="isLoading" class="loader" :style="{ borderColor: loaderColor }"></div>
        <div v-if="isLoading" class="loading-text">Loading data...</div>

        <div v-if="!isLoading">

            <ConfirmPopup></ConfirmPopup>
            <div class="d-flex flex-row-reverse bld-bar">
                <div class="py-1 px-2">
                    <WarningButton @click="saveProduct">
                        SAVE PRODUCT
                    </WarningButton>
                </div>
                <div class="p-2 px-5">ID: {{ this.ID }}</div>
            </div>
            <Toast />
            <div class="container">
                <div class="container-sm">
                    <p class="p-3 h2">{{ this.title?.length ? this.title : "No Titles" }}</p>
                </div>

                <!-- Butoane selectie  -->
                <div class="container-sm d-flex flex-wrap">
                    <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                        <div v-for="(items, index) in this.product.data.panels" class="btn-group me-2 mt-2" role="group">
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
                            <p class="p-3 h4">{{ product.data.panels?.length ? selectedProduct.title : 'No title' }}</p>
                        </div>
                        <div class="pt-4 px-2">
                            <i @click="editCurrentPanel()" class="p-1 pi pi-file-edit hovered" data-bs-toggle="tooltip"
                                data-bs-placement="bottom" data-bs-title="Edit" style="font-size: 1rem"></i>
                        </div>
                        <div class="pt-4 px-2">
                            <i class="p-1 pi pi-clone hovered" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                data-bs-title="Clone" style="font-size: 1rem"></i>
                        </div>
                        <div class="pt-4 px-2">
                            <i @click="deleteElement('panel')" class="p-1 pi pi-trash hovered alerted-hover"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Delete"
                                style="font-size: 1rem"></i>
                        </div>
                    </div>
                </div>

                <!-- Container Content -->
                <div class="mt-5 mb-5">
                    <div class="container-sm p-2 border-1 border-secondary">
                        <div v-if="product.data.panels?.length" class="mt-2 border-bottom border-bottom">
                            <div v-for="(items, index) in selectedProductCategories" class="p-1">
                                <!-- div titlu + btn -->
                                <div class="d-flex mt-1">
                                    <div class="p-1 flex-grow-1">
                                        <p class="px-3 h5"><i class="pi pi-eye-slash hovered" data-bs-toggle="tooltip"
                                                data-bs-placement="bottom" data-bs-title="Hide"></i> {{ items.title }}</p>
                                    </div>
                                    <div class="p-1">
                                        <i class="p-1 pi pi-file-edit hovered" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom" data-bs-title="Edit" style="font-size: 1rem"></i>
                                    </div>
                                    <div class="p-1">
                                        <i class="p-1 pi pi-copy hovered" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom" data-bs-title="Copy" style="font-size: 1rem"></i>
                                    </div>
                                    <!-- <div class="p-1">
                                        <i class="p-1 pi pi-arrow-right-arrow-left hovered" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom" data-bs-title="Move" style="font-size: 1rem"></i>
                                    </div> -->
                                    <div class="p-1">
                                        <i class="p-1 pi pi-clone hovered" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom" data-bs-title="Clone" style="font-size: 1rem"></i>
                                    </div>
                                    <div class="p-1">
                                        <i @click="deleteElement('category', items)"
                                            class="p-1 pi pi-trash hovered alerted-hover" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom" data-bs-title="Delete" style="font-size: 1rem"></i>
                                    </div>
                                </div>
                                <!-- div poze grid -->
                                <!-- Vor fi input + dropdown -->
                                <div class="d-flex align-content-start flex-wrap">

                                    <!-- <div v-if="items.type == 'text' ||
                                        items.type == 'input' ||
                                        items.type == 'inputMulti'
                                        ">
                                        <div v-for="(op) in items.options" class="p-1">
                                            <input type="text" :placeholder="`${op.option.data.label}`">
                                        </div>
                                    </div> -->
                                    <div v-if="items.options?.length" class="p-4 images-layout">
                                        <div v-for="(op) in items.options" class="p-1">
                                            <img style="width: 160px; height: 160px;" :src="`${op.option.data.value}`"
                                                alt="" data-bs-toggle="tooltip" data-bs-title="Order 1">
                                        </div>
                                    </div>
                                </div>
                                <!-- edit attributes -->

                                <div class="d-flex p-4">
                                    <EditButton @click="editChoiseAttribute(items, index)">
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

            <Dialog v-model:visible="addNewProductButton" modal header="Product" :style="{ width: '60vw' }">

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
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab"
                        tabindex="0">

                        <!-- logica panels -->

                        <div class="container mb-3">
                            <div v-for="(value, index) in rulesPanel.logic.rules" :key="index" class="d-flex m-2">
                                <div class="p-2 w-100 p-2 m-2">
                                    <span class="fs-5 p-1">{{ index == 0 ? 'If' : 'Or' }}</span>
                                    <select v-model="selectedValues[index]" class="logic-list">
                                        <option>Select Step</option>
                                        <option v-for="op in rulesPanelPanels[0].panels" :value="op.id">{{ op.title }}
                                        </option>
                                    </select>
                                    <span v-if="getCategoriesAfterPanel(selectedValues[index])?.length">
                                        <span class="fs-5 p-1">'s custom option</span>
                                        <select v-model="selectedCategories[index]" class="logic-list">
                                            <option>Select Custom Option</option>
                                            <option v-for="op in getCategoriesAfterPanel(selectedValues[index])"
                                                :value="op.id">{{ op.title }}</option>
                                        </select>
                                        <span class="fs-5 p-1">is</span>
                                        <select v-model="ruleLogic[index]" class="logic-list">
                                            <option value="anychoice" selected>Any choice</option>
                                            <option value="oneof">One Of..</option>
                                            <option value="notequal">Not equal</option>
                                            <option
                                                v-for="op in getOptionsAfterPanelAndCategory(selectedValues[index], selectedCategories[index])"
                                                :value="op.id">{{ op.option.data.label }}</option>
                                        </select>
                                    </span>

                                    <div>
                                        <span v-if="ruleLogic[index] == 'oneof' || ruleLogic[index] == 'notequal'">
                                            <div v-for="op in getOptionsAfterPanelAndCategory(selectedValues[index], selectedCategories[index])"
                                                class="p-2 m-2">
                                                <label class="fs-6" for="checkBox">
                                                    <input type="checkbox" name="checkbox">
                                                    {{ op.option.data.label }}
                                                </label>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div @click.stop="deleteCurrentLogic(index)"
                                    class="p-1 flex-shrink-1 btn-delete-logic border border-warning">
                                    <div class="align-btn-text">
                                        <span>delete</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="p-1 d-flex justify-content-center">
                            <PrimaryButton @click="createNewRule('panel')">
                                Add new rule
                            </PrimaryButton>
                        </div>

                        <div class="container mb-3">
                            <span class="fs-5 p-1">
                                then this layer should be:
                            </span>
                            <div class="p-1 d-flex justify-content-center">
                                <select class="logic-list-show">
                                    <option>Not Selected</option>
                                    <option>Shown</option>
                                    <option>Hidden</option>
                                </select>
                            </div>
                        </div>
                    </div>
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
                                <select v-model="typeCategory"
                                    class="form-select form-select-lg mb-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    aria-label="Large select example">
                                    <option value="img" selected>Image Thumbnail</option>
                                    <option value="text">Text Field</option>
                                    <option value="input">Text Monogram Field</option>
                                    <option value="select">Dropdown</option>
                                    <option value="multiple">Multiple Select</option>
                                    <option value="single">Single</option>
                                    <option value="inputMulti">Multiple inputs</option>
                                </select>
                            </div>

                            <!-- const CAT_TYPE = [
                                'img' => 1,
                                'text' => 2,
                                'input' => 3,
                                'inputMulti' => 4,
                                'single' => 1,
                                'multiple' => 5,
                                'select' => 6,
                            ]; -->

                            <div class="p-3">
                                <InputLabel for="extra_class_stepCategory" value="Extra class" />

                                <TextInput v-model="extra_class_stepCategory" type="text" class="mt-1 block w-full"
                                    autofocus autocomplete="" />

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
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab"
                        tabindex="0">
                        ...</div>
                </div>

                <!-- logic -->
                <!-- Apply & Cancel -->
            </Dialog>
            <!-- dialog confirm delete -->
            <Dialog v-model:visible="deleteElementVar" modal header="Confirm message" :style="{ width: '40vw' }">
                <div class="d-flex m-3">
                    <p class="h6">
                        Are you sure to delete it?
                    </p>
                </div>
                <div class="d-flex flex-row m-3">
                    <div class="p-2">
                        <EditButton @click="cancelConfirmation()">
                            CANCEL
                        </EditButton>
                    </div>
                    <div class="p-2">
                        <EditButton @click="sendConfirmation()">
                            CONFIRM
                        </EditButton>
                    </div>
                </div>

            </Dialog>
            <!-- modal add new category option -->

            <Dialog v-model:visible="createNewCategoryOption" modal header="Edit Choise Attribute"
                :style="{ width: '95vw' }">

                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><strong>
                                <a href="#">{{ title }}</a>
                            </strong></li>
                        <li class="breadcrumb-item active" aria-current="page"><strong>
                                {{ selectedProduct.title }}</strong></li>

                        <!-- get title of current category -->
                        <li class="breadcrumb-item active" aria-current="page">
                            <strong>
                                <a @click="newOptionRequest = false">{{
                                    selectedProductCategories[this.selectCurrentProductCategoryIndex].title }}</a>
                            </strong>
                        </li>
                    </ol>
                </nav>

                <!-- Option create - false -->
                <div v-if="!newOptionRequest">
                    <div class="d-flex">
                        <div class="p-2 flex-grow-1">
                            <TextInput v-model="title_productCategory" placeholder="Search..." type="text"
                                class="mt-1 block w-full" autofocus autocomplete="" />
                        </div>
                        <div class="p-3">
                            <EditButton>
                                Reorder
                            </EditButton>
                        </div>
                        <div class="p-3">
                            <EditButton @click="newOptionRequest = true">
                                New Option
                            </EditButton>
                        </div>
                    </div>

                    <!-- tabel optiuni -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="image-td-align" scope="col">Option</th>
                                <th scope="col">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="pointer-hover"
                                v-for="(items, index) in selectedProductCategories[this.selectCurrentProductCategoryIndex].options">
                                <td class="image-td-align">
                                    <div class="p-4">
                                        <div class="p-1 d-flex justify-content-center">
                                            <img style="width: 51px; height: 51px;" :src="`${items.option.data.value}`"
                                                alt="" data-bs-toggle="tooltip" data-bs-title="Order 1">
                                        </div>
                                        <span style="text-align: center;" class="image-td-align wrapped-text">
                                            {{ items.sku?.length ? '[ ' + items.sku + ' ]' : '' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="text-td-align">
                                    <div @click="editChosedOption(items, index)" class="p-1">
                                        <i class="p-1 pi pi-file-edit" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            data-bs-title="Edit" style="font-size: 1rem"></i>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Option create - false END -->


                <div v-if="newOptionRequest">
                    <div class="p-3">
                        <InputLabel for="optionLabel" value="Option Label" />

                        <TextInput v-model="optionLabel" type="text" class="mt-1 block w-full" autofocus autocomplete="" />

                    </div>

                    <div class="p-3">
                        <InputLabel for="" value="Thumbnail" />

                        <div>
                            <div id="drop-area" @dragover.prevent="handleDragOver" @dragleave.prevent="handleDragLeave"
                                @drop.prevent="handleDrop">
                                <p>Drag & drop a PNG, JPG, or JPEG file here or click to select one.</p>
                                <input type="file" id="file-input" accept=".png, .jpg, .jpeg" @change="handleFileSelect" />

                                <div class="d-flex justify-content-center">
                                    <img v-if="imagePreview" :src="imagePreview" alt="Image Preview" id="image-preview" />
                                </div>
                            </div>

                            <h4 class="m-1" style="text-align: center; font-weight: bold; font-style: italic;">OR</h4>

                            <div class="p-1 d-flex justify-content-center">
                                <PrimaryButton class="ml-4">
                                    Select an image from gallery
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>

                    <div class="p-3">
                        <InputLabel for="optionSKU" value="SKU" />

                        <TextInput v-model="optionSKU" type="text" class="mt-1 block w-full" autofocus autocomplete="" />

                    </div>

                    <div class="d-flex flex-row m-3">
                        <div class="p-2">
                            <EditButton @click="cancelNewOption()">
                                BACK
                            </EditButton>
                        </div>
                        <div class="p-2">
                            <EditButton @click="generateNewOption()">
                                APPLY
                            </EditButton>
                        </div>
                    </div>

                </div>
                <!-- logic -->
                <!-- Apply & Cancel -->
            </Dialog>
        </div>
        <div v-if="isOverlayVisible" class="overlay"></div>
    </AuthenticatedLayout>
</template>
