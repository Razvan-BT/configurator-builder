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
import TextareaInput from '@/components/TextareaInput.vue';
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
            categoryDescription: "",

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
            editCategory: false, // verifica daca am logica

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

            rulesCategory: {
                logic: {
                    rules: []
                },
                action: "show"
            },

            rulesCategoryOption: {
                logic: {
                    rules: []
                },
                action: "show"
            },

            // panel
            rulesPanelPanels: [],
            selectedValues: {},
            selectedValuesLogicRule: {},
            rulesPanelCategories: [],
            selectedCategories: {},

            panelLogicOptions: [],
            optionLogicOptionsCategories: [],
            ruleLogicOptions: [],

            // category
            selectedValueCategory: {},
            selectedValueCategoryLogicRule: {},
            ruleLogicCategory: {},
            selectedCategoriesCategory: {},

            // options
            selectedValueCategoryOption: {},
            selectedValueCategoryOptionLogicRule: {},
            ruleLogicCategoryOption: {},
            selectedCategoriesCategoryOption: {},

            logicVisible_panels: 'show', // seteaza vizibilitatea optiuni care are logica, panels
            logicVisible_category: 'show',
            logicVisible_options: 'show',

            cloneElement: false,
            cloneElementType: 0,
            cloneElementId: -1,

            cloneElementToPanel: false,
            cloneElementToPanelId: '',
            cloneElementToPanelCategory: -1,

            checkPanelOption: true,
        
            exportElements: false,
            exportData: '',
            exportDatalocal: localStorage.getItem('productCopyed') || '-',
        }
    },
    methods: {
        importElementsFunc() {
            if(this.exportDatalocal != null && this.exportDatalocal != '') {
                if(this.exportDatalocal == 'panou') {

                    let productJSON = localStorage.getItem('productJSON');
                    if(productJSON != null) this.product.data.panels.push(JSON.parse(localStorage.getItem('productJSON').replaceAll(localStorage.getItem('productID'), this.makeid(17))));
                    
                    localStorage.removeItem('productCopyed');
                    localStorage.removeItem('productID'); // pentru replacement
                    localStorage.removeItem('productJSON'); 
                    this.exportDatalocal = '';
                    
                    this.createToast({
                        type: 'success',
                        title: 'Info message',
                        details: "Panel imported!"
                    });

                } else if(this.exportDatalocal == 'category') {

                    if(!this.selectedProduct) {
                        this.createToast({
                            type: 'error',
                            title: 'Info message',
                            details: "Please first select the product!"
                        });

                        return;
                    }

                    let productJSON = localStorage.getItem('productJSON');
                    const element = JSON.parse(productJSON);

                    // console.log("Get panelId to replace", element.panelId);
                    if(productJSON != null) this.selectedProductCategories.push(JSON.parse(localStorage.getItem('productJSON').replaceAll(localStorage.getItem('productID'), this.makeid(17)).replaceAll(element.panelId, this.selectedProduct.id)));

                    localStorage.removeItem('productCopyed');
                    localStorage.removeItem('productID'); // pentru replacement
                    localStorage.removeItem('productJSON'); 
                    this.exportDatalocal = '';

                    this.createToast({
                        type: 'success',
                        title: 'Info message',
                        details: "Category imported!"
                    });

                }
            }
        },
        
        exportElementsFunc(index, type) {
            switch(type) {
                case 'panel': {
                    this.exportElements = true;
                    this.exportData = JSON.stringify(this.selectedProduct);
                    console.log("[PANOU] this.exportData", true);

                    localStorage.setItem('productCopyed', 'panou');
                    localStorage.setItem('productID', this.selectedProduct.id); // pentru replacement
                    localStorage.setItem('productJSON', this.exportData);
                    break;
                }

                case 'category': {
                    this.exportElements = true;
                    this.exportData = JSON.stringify(this.selectedProductCategories[index]);
                    console.log("[CATEGORY] this.exportData", true);

                    localStorage.setItem('productCopyed', 'category');
                    localStorage.setItem('productID', this.selectedProductCategories[index].id); // pentru replacement
                    localStorage.setItem('productJSON', this.exportData);
                    break;
                }

                default: {
                    console.error("Unknown type of element to export..");
                    this.exportElements = false;
                    this.exportData = '';

                    localStorage.removeItem('productCopyed');
                    localStorage.removeItem('productID'); // pentru replacement
                    localStorage.removeItem('productJSON');
                    break;
                }
            }
        },

        simulateLoading() {
            this.isLoading = true;
            // setTimeout(() => {
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
            // }, 3000); // Simulating a 3-second loading time
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
                categorie
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
                    type: "panel",
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
                    this.selectedValuesLogicRule[returnIndex] = "||";
                    this.selectedCategories[returnIndex] = 'Select Custom Option';
                    this.ruleLogic[returnIndex] = 'any';
                }

                if(this.editPanel) {
                    this.selectedProduct.logic = this.rulesPanel.logic;
                }

            } else if (where == 'category') {

                // preiau paneluriile pentru category dupa index ID
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
                    type: "panel",
                    option: "oneof",
                    operator: "||",
                    layer: "",
                    active: "",
                    logicRuleLabel: "",
                    options: []

                }
                this.rulesCategory.logic.action = this.logicVisible_category;
                this.rulesCategory.logic.rules.push(obj);

                // this.selectedProduct.logic = this.rulesPanel.logic;

                let returnIndex = this.rulesCategory.logic.rules.length;
                if (returnIndex == 0) this.selectedValueCategory[0] = 'Select Step';
                else {
                    this.selectedValueCategory[returnIndex] = 'Select Step';
                    this.selectedValueCategoryLogicRule[returnIndex] = "||";
                    this.selectedCategoriesCategory[returnIndex] = 'Select Custom Option';
                    this.ruleLogicCategory[returnIndex] = 'any';
                }

            } else if (where == 'option') {

                // option 
                // preiau paneluriile pentru category option dupa index ID
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
                    type: "panel",
                    layer: "",
                    active: "",
                    logicRuleLabel: "",
                    options: []

                }
                this.rulesCategoryOption.logic.action = "show";
                this.rulesCategoryOption.logic.rules.push(obj);

                // this.selectedProduct.logic = this.rulesPanel.logic;

                let returnIndex = this.rulesCategoryOption.logic.rules.length;
                if (returnIndex == 0) this.selectedValueCategoryOption[0] = 'Select Step';
                else {
                    this.selectedValueCategoryOption[returnIndex] = 'Select Step';
                    this.selectedValueCategoryOptionLogicRule[returnIndex] = '||';
                    this.selectedCategoriesCategoryOption[returnIndex] = 'Select Custom Option';
                    this.ruleLogicCategoryOption[returnIndex] = 'any';
                }

                if(this.editOption) {
                    this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].logic = this.rulesCategoryOption.logic;
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

        getOptionsAfterPanelAndCategoryIfChecked(panel, caty, ruleId) {
            let obj = [];
            this.product.data.panels.filter((value, index) => {
                if (value.id == panel) {
                    value.categories.filter((cat, idx) => {
                        if (cat.id == caty) obj.push(cat.options);
                    })

                    if (this.editPanel) {
                        setTimeout(() => {
                            this.checkBoxPanels(ruleId);
                        }, 3000);
                    }
                }
            })

            return obj[0];
        },

        checkBoxPanels(ruleId) {
            // filtrez checked
            console.log(
                "Debug ruleId", ruleId
            )
            // this.selectedProduct.forEach((value, index) => {
                this.selectedProduct.logic.rules.filter((rulesf, idx) => {
                    if (idx == ruleId) {
                        if (rulesf.options?.length) {
                            rulesf.options.filter((fs, idx23) => {

                                // panels
                                let elements = document.querySelectorAll('.checkedPanelOp_' + ruleId);

                                for (let i = 0; i < elements.length; i++) {
                                    if (elements[i].getAttribute('data-option') == fs) {
                                        elements[i].setAttribute('checked', "true");
                                    }
                                }

                            })
                        }
                    }
                })
            // })

            // category

            // this.selectedProductCategories.filter((value, index) => {
            if(this.selectCurrentProductCategoryIndex != -1 && this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic.rules?.length) {
                this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic.rules.filter((rulesf, idx) => {
                    if (idx == ruleId) {
                        if (rulesf.options?.length) {
                            rulesf.options.filter((fs, idx23) => {

                                console.log("Debug category checkboxes", fs)

                                // category
                                let category = document.querySelectorAll('.checkedCategoryOp_' + ruleId);

                                for (let i = 0; i < category.length; i++) {
                                    if (category[i].getAttribute('data-option') == fs) {
                                        category[i].setAttribute('checked', "true");
                                    }
                                }

                            })
                        }
                    }
                })
            }
            // })
        },

        checkBoxOptions(ruleId) {
            // options
            //  option.filter((value, index) => {
            this.rulesCategoryOption.logic.rules.filter((rulesf, idx) => {
                if (idx == ruleId) {
                    if (rulesf.options?.length) {
                        rulesf.options.filter((fs, idx23) => {

                            // category
                            let options = document.querySelectorAll('.checkedOptionsOp_' + ruleId);

                            for (let i = 0; i < options.length; i++) {
                                if (options[i].getAttribute('data-option') == fs) {
                                    options[i].setAttribute('checked', "true");
                                }
                            }

                        })
                    }
                }
            })
            // })
        },

        // panels
        checkBoxOptionsPanel(event, option, index) {
            console.log("[checkBoxOptionsPanel] Debug options", option)
            if (this.editPanel) {
                if (this.selectedProduct) {
                    // daca este true 
                    if (event.target.checked) {
                        if (this.selectedProduct.logic.rules?.length) {
                            this.selectedProduct.logic.rules.forEach((e, idx) => {
                                if (index == idx) {
                                    console.log(" this.rulesPanel", this.rulesPanel)
                                    if (this.rulesPanel.logic.rules[idx].options.indexOf(option.id) === -1)
                                        this.selectedProduct.logic.rules.options = this.rulesPanel.logic.rules[idx].options.push(option.id);
                                };
                            });
                        }
                    } else {

                        if (this.selectedProduct.logic.rules?.length) {
                            this.selectedProduct.logic.rules.forEach((e, idx) => {
                                if (index == idx) {
                                    this.rulesPanel.logic.rules[idx].options.filter((op, idx2) => {
                                        if (op == option.id) {
                                            if (this.rulesPanel.logic.rules[idx].options.indexOf(option.id) !== -1)
                                            this.selectedProduct.logic.rules.options = this.rulesPanel.logic.rules[idx].options.splice(idx2, 1);
                                        }
                                    });
                                }
                            });
                        }
                    }
                    console.log("Check box option panel: ", option, this.rulesPanel.logic.rules[index].options, index);
                }
            } else {

                // daca este creata o regula -> fara a edita panelul

                if (event.target.checked) {
                    if (!this.rulesPanel.logic.rules[index].options?.length) {

                        this.rulesPanel.logic.rules[index].options.push(option.id);

                    } else {

                        if (this.rulesPanel.logic.rules[index].options.indexOf(option.id) === -1)
                            this.selectedProduct.logic.rules.options = this.rulesPanel.logic.rules[index].options.push(option.id);

                    }
                } else {
                    if (this.rulesPanel.logic.rules[index].options?.length) {

                            this.selectedProduct.logic.rules.options = this.rulesPanel.logic.rules[index].options.push(option.id);

                    }

                }
            }
        },

        // category
        checkBoxOptionsCategory(event, option, index) {
            if (this.editCategory) {
                console.log("[checkBoxOptionsCategory] Debug options", this.selectedProductCategories)
                if (this.selectedProductCategories) {
                    // daca este true 
                    if (event.target.checked) {
                        if (this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic.rules?.length) {
                            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic.rules.forEach((e, idx) => {
                                if (index == idx) {
                                    console.log(" this.rulesCategory", this.selectedProductCategories)
                                    if (this.rulesCategory.logic.rules[idx].options.indexOf(option.id) === -1)
                                        this.rulesCategory.logic.rules[idx].options.push(option.id);
                                };
                            });
                        }
                    } else {

                        if (this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic.rules?.length) {
                            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic.rules.forEach((e, idx) => {
                                if (index == idx) {
                                    this.rulesCategory.logic.rules[idx].options.filter((op, idx2) => {
                                        if (op == option.id) {
                                            if (this.rulesCategory.logic.rules[idx].options.indexOf(option.id) !== -1)
                                                this.rulesCategory.logic.rules[idx].options.splice(idx2, 1);
                                        }
                                    });
                                }
                            });
                        }
                    }
                    
                } 
            } else {
                    // daca este creata o regula -> fara a edita categoria

                    if (event.target.checked) {
                        if (!this.rulesCategory.logic.rules[index].options?.length) {

                            this.rulesCategory.logic.rules[index].options.push(option.id);

                        } else {

                            if (this.rulesCategory.logic.rules[index].options.indexOf(option.id) === -1)
                                this.rulesCategory.logic.rules[index].options.push(option.id);

                        }
                    } else {
                        if (this.rulesCategory.logic.rules[index].options?.length) {

                            this.rulesCategory.logic.rules[index].options.push(option.id);

                        }

                    }
                    console.log("Check box option category: ", option, this.rulesCategory.logic.rules[index].options, index);
                }
        },

        // options
        checkBoxOptionsOption(event, option, index) {
            if (this.editOption) {
                if (option) {
                    // daca este true 
                    if (event.target.checked) {
                        if (this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options?.length) {
                            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options.forEach((e, idx) => {
                                console.log("Optiune debug", e);
                                if (idx == this.editOptionID) {
                                    e.logic.rules.forEach((oRusle, rId) => {
                                        if (index == rId) {
                                            if (this.rulesCategoryOption.logic.rules[rId].options.indexOf(option.id) === -1)
                                                this.rulesCategoryOption.logic.rules[rId].options.push(option.id);
                                        };
                                    });
                                }
                            });
                        }
                    } else {

                        if (this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options?.length) {
                            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options.forEach((e, idx) => {
                                if (idx == this.editOptionID) {
                                    e.logic.rules.forEach((oRusle, rId) => {
                                        if (index == rId) {
                                            if (oRusle.options?.length) {
                                                oRusle.options.forEach((opt, optIdx) => {
                                                    if (opt == option.id) {
                                                        if (this.rulesCategoryOption.logic.rules[rId].options.indexOf(option.id) !== -1)
                                                            this.rulesCategoryOption.logic.rules[rId].options.splice(optIdx, 1);
                                                    }
                                                })
                                            }
                                        };
                                    });
                                }
                            });
                        }
                    }
                    console.log("Check box option panel: ", option, this.rulesCategoryOption.logic.rules[index].options, index);
                }
            } else {

                // cand creez o optiune noua -> fara a fi editata.
                if (event.target.checked) {
                    if (!this.rulesCategoryOption.logic.rules[index].options?.length) {

                        this.rulesCategoryOption.logic.rules[index].options.push(option.id);

                    } else {

                        if (this.rulesCategoryOption.logic.rules[index].options.indexOf(option.id) === -1)
                            this.rulesCategoryOption.logic.rules[index].options.push(option.id);

                    }
                } else {
                    if (this.rulesCategoryOption.logic.rules[index].options?.length) {

                        this.rulesCategoryOption.logic.rules[index].options.push(option.id);

                    }

                }

            }
        },


        deleteCurrentLogic(index) {
            delete this.selectedValues[index];
            delete this.selectedValuesLogicRule[index];
            delete this.selectedCategories[index];
            delete this.ruleLogic[index];

            console.log("Delete ITEM: ", this.rulesPanel.logic.rules.splice(index, 1));
            // this.rulesPanel.splice(index, 1);

            this.rulesPanel.logic.rules.forEach((value, idx) => {
                this.selectedValues[index] = value.panel;
                this.selectedCategories[index] = value.category;
                this.selectedValuesLogicRule[index] = value.operator;
                this.ruleLogic[index] = 'any';
            });
        },

        // logic for panel END

        // logic category start
        deleteCurrentLogicCategory(index) {
            delete this.selectedValueCategory[index];
            delete this.selectedValueCategoryLogicRule[index];
            delete this.selectedCategoriesCategory[index];
            delete this.ruleLogicCategory[index];

            console.log("Delete ITEM: ", this.rulesCategory.logic.rules.splice(index, 1));

            this.rulesCategory.logic.rules.forEach((value, idx) => {
                this.selectedValueCategory[index] = value.panel;
                this.selectedCategoriesCategory[index] = value.category;
                this.selectedValueCategoryLogicRule[index] = value.operator;
                this.ruleLogicCategory[index] = 'any';
            });
        },
        // end
        // logic cat option start
        deleteCurrentLogicCategoryOption(index) {
            delete this.selectedValueCategoryOption[index];
            delete this.selectedValueCategoryOptionLogicRule[index];
            delete this.selectedCategoriesCategoryOption[index];
            delete this.ruleLogicCategoryOption[index];

            console.log("Delete ITEM: ", this.rulesCategoryOption.logic.rules.splice(index, 1));

            this.rulesCategoryOption.logic.rules.forEach((value, idx) => {
                this.selectedValueCategoryOption[index] = value.panel;
                this.selectedCategoriesCategoryOption[index] = value.category;
                this.selectedValueCategoryOptionLogicRule[index] = value.operator;
                this.ruleLogicCategoryOption[index] = 'any';
            });
        },
        // end
        async saveProduct() {
            // console.log("TEEST", this.selectedProduct.logic)
            let errorSaving = false;

            if(!this.product.data.panels?.length) {
                this.createToast({
                    type: 'error',
                    title: 'Info message',
                    details: "Error to saving your progress... because you have an product created."
                }); 
                errorSaving = true;
            }

            this.product.data.panels.filter((i) => {
                    if(!i.categories.length) {
                        this.createToast({
                        type: 'error',
                        title: 'Info message',
                        details: "Error to saving your progress... because you have an product without category."
                    }); 
                    errorSaving = true;
                }
            });
            
            if(errorSaving) return;
            this.isOverlayVisible = true;

            //  refac default options
            let arrayWithPanels = {};
            this.product.data.panels.forEach((panels, index) => {
                panels.categories.filter((cat, idx) => {
                    arrayWithPanels[cat.id] = cat.panelId;
                })
            })

            const defaultObject = {};
            const differentObject = {};

            for (const key in arrayWithPanels) {
                const value = arrayWithPanels[key];
                if (!defaultObject.hasOwnProperty(value)) {
                    defaultObject[value] = {};
                }
                defaultObject[value][key] = [];
            }

            for (const value in defaultObject) {
                if (Object.keys(defaultObject[value]).length > 1) {
                    differentObject[value] = defaultObject[value];
                }
            }

            // console.log("obj", defaultObject);
            this.product.settings.defaultOptions = defaultObject;
            
            // end
            let data = {
                configuratorId: this.ID,
                data: this.product,
                by: usePage().props.auth.user.name
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
            if (path?.length && path.length > 1) this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].option.data.value = path;
            else if (path == 'delete') this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].option.data.value = '';
            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].logic = this.rulesCategoryOption.logic;
            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].logic.action = this.logicVisible_options;
            console.log("Razvan debug 22", this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID]);

            this.editOption = false;
            this.editOptionID = -1;
            this.optionSKU = '';
            this.optionLabel = '';
            this.selectedFile = null;
            this.newOptionRequest = false;
            this.createNewCategoryOption = false;
            this.imagePreview = '';
            if (this.selectedFile) this.selectedFile = '';
            this.createToast({
                type: 'info',
                title: 'Info message',
                details: "Option edited successfull!"
            });
        },


        deleteChosedOption(option, index) {
            // delete current option
            // tin option pentru debug, index pentru delete.

            if (index >= 0) {
                this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options.filter((value, idx) => {
                    if (option.id == value.id) {
                        // delete prima optiune selectata.
                        this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options.splice(idx, 1);

                        // reset tot 
                        this.optionSKU = "";
                        this.imagePreview = "";
                        this.optionLabel = "";

                        // resetez logica pt optiune
                        this.rulesCategoryOption.logic = {
                            rules: [],
                            action: 'hide'
                        };

                        this.selectedValueCategoryOption = {};
                        this.selectedValueCategoryOptionLogicRule = {};
                        this.selectedCategoriesCategoryOption = {};
                        this.ruleLogicCategoryOption = {};

                        this.createToast({
                            type: 'warn',
                            title: 'Info message',
                            details: "Option deleted succesfully!"
                        });

                        this.newOptionRequest = false;
                        this.createNewCategoryOption = false;
                        this.selectCurrentProductCategoryIndex = -1;
                    }
                });
            }
        },


        editChosedOption(option, index) {
            // repopulez input dupa save --> doar optiunea selectata
            this.newOptionRequest = true;
            this.editOption = true;
            this.editOptionID = index;
            this.optionSKU = option.sku;
            this.imagePreview = option.option.data.value;
            this.optionLabel = option.option.data.label;
            this.logicVisible_options = option.logic.action;

            // resetez logica pt optiune
            this.rulesCategoryOption.logic = {
                rules: [],
                action: this.logicVisible_options
            };

            this.selectedValueCategoryOption = {};
            this.selectedValueCategoryOptionLogicRule = {};
            this.selectedCategoriesCategoryOption = {};
            this.ruleLogicCategoryOption = {};

            //  repopulez cu optiuni de logica daca are 
            if (option.logic.rules?.length) {
                this.rulesCategoryOption.logic = option.logic;
            }

            this.rulesPanelPanels = [];
            let panels = {};
            this.panelLogicOptions.filter((value, index) => {
                panels = {
                    panels: this.product.data.panels,
                }
            });
            this.rulesPanelPanels.push(panels);
            
            this.initLogic();
            console.log("editChosedOption", this.product.data.panels)
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
                this.selectedFile = files[0];
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

            this.rulesPanel.logic = {
                rules: [],
                action: 'hide'
            };

            this.selectedValues = {};
            this.selectedValuesLogicRule = {};
            this.selectedCategories = {};
            this.ruleLogic = {};

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

        cloneElementConfirm(index, type) {

            if (type !== 3) {
                this.cloneElement = true;
                this.cloneElementId = index;
                this.cloneElementType = type;
            } else {
                this.cloneElementToPanelCategory = index;
                this.cloneElementToPanel = true;
            }
        },

        confirmClone() {
            let makeId = this.makeid(1);
            if (this.cloneElementToPanelId != '') {
                let currentElement = JSON.parse(JSON.stringify(this.selectedProductCategories[this.cloneElementToPanelCategory]));
                let id = currentElement.id;

                this.product.data.panels.filter((value, index) => {
                    if (value.id == this.cloneElementToPanelId) {
                        value.categories.push(JSON.parse(JSON.stringify(currentElement).replaceAll(id, id + makeId)))
                    }
                })

                currentElement = {};
            }

            if (this.cloneElementType == 2) {
                // clonez categorya
                let currentElement = JSON.parse(JSON.stringify(this.selectedProductCategories[this.cloneElementId]));
                if (currentElement) {
                    // schimb id-uri pentru optiuni.
                    let options = [];
                    this.selectedProductCategories[this.cloneElementId].options.filter((op) => {
                        options.push(JSON.parse(JSON.stringify(op).replaceAll(op.id, op.id + makeId)))
                    });
                    let id = currentElement.id;
                    currentElement.options = options;
                    currentElement.title += ' - COPY';
                    this.selectedProduct.categories.push(JSON.parse(JSON.stringify(currentElement).replaceAll(id, id + makeId)));
                    currentElement = {};
                }
            } else if (this.cloneElementType == 1) {

                let currentElement = JSON.parse(JSON.stringify(this.selectedProduct));
                if (currentElement) {
                    let categories = [], options;
                    this.selectedProduct.categories.filter((cat) => {
                        categories.push(JSON.parse(JSON.stringify(cat).replaceAll(cat.id, cat.id + makeId)))
                    });
                    let id = currentElement.id;
                    currentElement.categories = categories;
                    currentElement.title += ' - COPY';
                    this.product.data.panels.push(JSON.parse(JSON.stringify(currentElement).replaceAll(id, id + makeId)));
                    currentElement = {};
                }
            }

            this.cloneElement = false;
            this.cloneElementType = 0; // 1 - panel 2 - category
            this.cloneElementId = -1; // id panel or category

            // clone category to panel;
            this.cloneElementToPanel = false;
            this.cloneElementToPanelId = '';
            this.cloneElementToPanelCategory = -1;

            console.log("Confirm clone")
        },


        cancelConfirmation() {
            this.deleteElementVar = false;
            this.deleteElementId = '';
            this.deleteElementCat = '';

            //  clone element\
            this.cloneElement = false;
            this.cloneElementType = {}; // 1 - panel 2 - category
            this.cloneElementId = -1; // id panel or category

            // clone category to panel;
            this.cloneElementToPanel = false;
            this.cloneElementToPanelId = '';
            this.cloneElementToPanelCategory = -1;
        },

        sendConfirmation() {
            switch (this.deleteElementCat) {
                case 'panel': {
                    this.product.data.panels.filter((el, index) => {
                        if (this.deleteElementId.id == el.id && this.selectedProduct.id == el.id) {
                            console.log("Razvan delete panel", el)
                            this.product.data.panels.splice(index, 1);
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
                            this.selectedProductCategories.splice(index, 1);
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
                    type: 'info',
                    title: 'Info message',
                    details: this.title_product.toUpperCase() + " was edited succesfully!"
                });

                this.selectedProduct.title = this.title_product || "";
                this.selectedProduct.skuPrefix = this.sku_prefix || "";
                this.selectedProduct.description = "";
                this.selectedProduct.extraClassName = this.extra_class_step || "";
                this.selectedProduct.logic = this.rulesPanel.logic || {};
                this.selectedProduct.logic.action = this.logicVisible_panels || 'show';

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

            this.selectedValues = {};
            this.selectedValuesLogicRule = {};
            this.selectedCategories = {};
            this.ruleLogic = {};

            this.rulesPanel.logic = {
                rules: [],
                action: 'hide'
            };

            console.log("End createNewProduct", object);
        },

        initGlobalObject(data) {
            console.log("initGlobalObject init", data.data.data.panels);
            this.product.data.panels = data.data.data.panels;

            this.isLoading = false;
        },

        initLogic() {
            // reinit logic
            console.log("Init logic")
            // panel
            if (!this.editCategory) {
                this.repopulateLogic();
                this.rulesPanelPanels = [];
                let panels = {};
                this.panelLogicOptions.filter((value, index) => {
                    panels = {
                        panels: this.product.data.panels,
                    }
                });

                this.rulesPanelPanels.push(panels);
                if (this.editPanel) {
                    if (this.rulesPanel.logic.rules?.length) {
                        this.rulesPanel.logic.rules.forEach((value, index) => {
                            this.selectedValues[index] = value.panel;
                            this.selectedValuesLogicRule[index] = value.operator;
                            this.selectedCategories[index] = value.category;
                            this.ruleLogic[index] = value.option;
                            setTimeout(() => {
                                this.checkBoxPanels(index);
                            }, 3000);
                        })
                    }
                    this.logicVisible_panels = this.selectedProduct.logic.action;
                }
            } else {
                // init category
                if (this.rulesCategory.logic.rules?.length) {
                    this.rulesCategory.logic.rules.forEach((value, index) => {
                        console.log("Init logic category", value)
                        this.selectedValueCategory[index] = value.panel;
                        this.selectedValueCategoryLogicRule[index] = value.operator;
                        this.selectedCategoriesCategory[index] = value.category;
                        this.ruleLogicCategory[index] = value.option;
                        setTimeout(() => {
                            this.checkBoxPanels(index);
                        }, 3000);
                    })
                }
                this.logicVisible_category = this.rulesCategory.logic.action;
            }
            if (this.editOption) {
                // init option
                if (this.rulesCategoryOption.logic.rules?.length) {
                    this.rulesCategoryOption.logic.rules.forEach((value, index) => {
                        console.log("Init logic category option", value)
                        this.selectedValueCategoryOption[index] = value.panel;
                        this.selectedValueCategoryOptionLogicRule[index] = value.operator;
                        this.selectedCategoriesCategoryOption[index] = value.category;
                        this.ruleLogicCategoryOption[index] = value.option;
                        setTimeout(() => {
                            this.checkBoxOptions(index);
                        }, 5000);
                    })
                }
            }
        },

        createNewOptionForProduct() {
            if (this.product.data?.panels) {
                console.log("createNewOptionForProduct init");
                this.createNewCategory = true;

                this.rulesPanelPanels = [];
                let panels = {};
                this.panelLogicOptions.filter((value, index) => {
                    panels = {
                        panels: this.product.data.panels,
                    }
                });

                this.rulesPanelPanels.push(panels);

                this.rulesCategory.logic = {
                    rules: [],
                    action: this.logicVisible_category
                }

                this.selectedValueCategory = {};
                this.selectedValueCategoryLogicRule = {};
                this.selectedCategoriesCategory = {};
                this.ruleLogicCategory = {};

                this.title_productCategory = '';
                this.categoryDescription = '';
                this.typeCategory = 'select';
                this.extra_class_stepCategory = '';

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
            this.categoryDescription = "";
            this.selectCurrentProductCategoryIndex = -1;
            this.editCategory = false;
        },

        // Cand se creeaza o noua categorie de produs
        createNewCategoryPanel() {
            if (!this.editCategory) {
                if (this.selectedProduct) {
                    this.selectedProduct.categories.push({
                        id: this.makeid(24),
                        zIndex: this.selectedProduct.categories?.length + 1, // order elements
                        type: this.typeCategory?.length ? this.typeCategory : "img",
                        title: this.title_productCategory || "",
                        description: this.categoryDescription || "",
                        extraClassName: this.extra_class_stepCategory || "",
                        panelId: this.selectedProduct ? this.selectedProduct.id : "",
                        panelID: this.selectedProduct ? this.selectedProduct.id : "",
                        options: [],
                        logic: this.rulesCategory.logic
                    });
                }

                this.createToast({
                    type: 'success',
                    title: 'Info message',
                    details: this.title_productCategory.toUpperCase() + " category added succesfully!"
                });
            } else {
                // to do --- urgent
                // edit an category
                if (this.selectedProduct) {
                    let title = this.selectedProductCategories[this.selectCurrentProductCategoryIndex].title;
                    if (title != null) {
                        this.createToast({
                            type: 'info',
                            title: 'Info message',
                            details: title.toUpperCase() + " category edited succesfully!"
                        });
                    }
                    this.selectedProductCategories[this.selectCurrentProductCategoryIndex].type = this.typeCategory?.length ? this.typeCategory : "img",
                        this.selectedProductCategories[this.selectCurrentProductCategoryIndex].title = this.title_productCategory || "",
                        this.selectedProductCategories[this.selectCurrentProductCategoryIndex].description = this.categoryDescription || "",
                        this.selectedProductCategories[this.selectCurrentProductCategoryIndex].extraClassName = this.extra_class_stepCategory || "",
                        this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic = this.rulesCategory.logic
                }
            }

            this.cancelCreateNewCategory();
        },

        getCurrentProduct(getItem, index) {
            console.log("getCurrentProduct", getItem);

            this.selectCurrentProductIndex = index;

            this.rulesPanel.logic.rules = [];
            this.selectedValues = {};
            this.selectedValuesLogicRule = {};
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
                            if (!this.editOption) this.addNewOption(response.data.server_host + '/storage/' + this.path_image);
                            else this.editCurrentOption(response.data.server_host + '/storage/' + this.path_image);
                        })
                        .catch(error => {
                            console.log(error);
                        });

                } else {

                    if (this.editOption) this.editCurrentOption('-');
                    else this.addNewOption('');

                }

            }
            // resetez valori dupa generare optione
            console.log(this.selectedProduct.categories[this.selectCurrentProductCategoryIndex])
        },


        deleteCurrentImage(option, index) {
            console.log("Delete current image (options)", option, index);
            this.selectedFile = '';
            this.imagePreview = '';
            this.path_image = '';
            this.editCurrentOption("delete");
        },

        createToast(details) {
            this.$toast.add({ severity: details.type, summary: details.title, detail: details.details, life: 3000 });
        },

        addNewOption(path) {
            //  adaug dupa response in alta functie deoarece nu imi permite requestul..
            this.selectedProductCategories[this.selectCurrentProductCategoryIndex].options.push({
                option: {
                    preview: {
                        type: 'img',
                    },
                    data: {
                        label: this.optionLabel,
                        inputMaxValue: 999999,
                        inputMinValue: 0,
                        inputStep: 1,
                        valuePreview: null,
                        value: path,
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

        editCurrentCategory(index) {
            console.log("editCurrentCategory xx2", this.selectedProductCategories[index]);
            // reset logica category

            this.editCategory = true;

            this.rulesPanelPanels = [];
            let panels = {};
            this.panelLogicOptions.filter((value, index) => {
                panels = {
                    panels: this.product.data.panels,
                }
            });

            this.rulesPanelPanels.push(panels);

            this.rulesCategory.logic = {
                rules: [],
                action: 'show',
            }

            this.selectedValueCategory = {};
            this.selectedValueCategoryLogicRule = {};
            this.selectedCategoriesCategory = {};
            this.ruleLogicCategory = {};

            this.createNewCategory = true;
            this.selectCurrentProductCategoryIndex = index;

            this.title_productCategory = this.selectedProductCategories[index].title ?? 'No Title';
            this.typeCategory = this.selectedProductCategories[index].type;
            this.extra_class_stepCategory = this.selectedProductCategories[index].extraClassName;
            this.categoryDescription = this.selectedProductCategories[index].description;
            this.logicVisible_category = this.selectedProductCategories[index].logic.action;

            this.rulesCategory.logic = this.selectedProductCategories[index].logic;
            this.initLogic();
        },

        editCurrentPanel() {
            console.log("editCurrentPanel xx2")
            if (this.product.data.panels?.length) {
                this.editPanel = true;
                this.addNewProductButton = true;
                this.editCategory = false;
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

                            if (result.data.data.panels?.length) result.data.data.panels.forEach((v, i) => {
                                if (v.id == this.selectedProduct.id) {

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
        logicVisible_panels: {
            handler(data) {
                if (this.selectedProduct) {
                    this.selectedProduct.logic.action = data;
                }
            }
        },
        logicVisible_category: {
            handler(data) {
                if (this.selectedProduct) {
                    this.selectedProductCategories[this.selectCurrentProductCategoryIndex].logic.action = data;
                }
            }
        },

        product: {
            handler(data) {
                console.log("Object base changed", this.product);
            }
        },
        newOptionRequest: {
            handler(data) {
                if (!data) {
                    console.log(data)
                    this.rulesCategoryOption.logic = {
                        rules: [],
                        action: 'hide'
                    };

                    this.selectedValueCategoryOption = {};
                    this.selectedValueCategoryOptionLogicRule = {};
                    this.selectedCategoriesCategoryOption = {};
                    this.ruleLogicCategoryOption = {};

                    this.optionSKU = '';
                    this.optionLabel = '';
                    this.selectedFile = null;
                    this.imagePreview = '';
                    this.logicVisible_options = '';
                    if (this.selectedFile) this.selectedFile = '';
                }
            }
        },
        // createNewCategoryOption: {
        //     handler(data) {
        //         // reset values from add option x btn
        //     }
        // },
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
        selectedValuesLogicRule: {
            deep: true,
            handler(newValues, oldValues) {
                for (let i in this.selectedValuesLogicRule) {

                    this.rulesPanel.logic.rules.filter((val, index) => {
                        if(index > 0) {
                            if (index == i) {
                                val.operator = newValues[i];
                                this.selectedValuesLogicRule[index - 1];
                            }
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
        // category
        selectedValueCategory: {
            deep: true,
            handler(newValues, oldValues) {
                for (let i in this.selectedValueCategory) {

                    this.rulesCategory.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.panel = newValues[i];
                            this.selectedValueCategory[index - 1];
                        }
                    });
                }
            },
        },
        selectedValueCategoryLogicRule: {
            deep: true,
            handler(newValues, oldValues) {
                for (let i in this.selectedValueCategoryLogicRule) {

                    this.rulesCategory.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.operator = newValues[i];
                            this.selectedValueCategoryLogicRule[index - 1];
                        }
                    });
                }
            },
        },
        selectedCategoriesCategory: {
            deep: true,
            handler(newValue, oldValue) {
                for (let i in this.selectedCategoriesCategory) {

                    console.log("Category changed", newValue[i])
                    this.rulesCategory.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.category = newValue[i];
                            this.selectedCategoriesCategory[index - 1];
                        }
                    });
                }
            }
        },
        ruleLogicCategory: {
            deep: true,
            handler(newValue, oldValue) {
                for (let i in this.ruleLogicCategory) {

                    console.log("Option rule changed", newValue[i])
                    this.rulesCategory.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.option = newValue[i];
                            this.ruleLogicCategory[index - 1];
                        }
                    });
                }
            }
        },
        // options
        selectedValueCategoryOption: {
            deep: true,
            handler(newValues, oldValues) {
                for (let i in this.selectedValueCategoryOption) {

                    this.rulesCategoryOption.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.panel = newValues[i];
                            this.selectedValueCategoryOption[index - 1];
                        }
                    });
                }
            },
        },
        selectedValueCategoryOptionLogicRule: {
            deep: true,
            handler(newValues, oldValues) {
                for (let i in this.selectedValueCategoryOptionLogicRule) {

                    this.rulesCategoryOption.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.operator = newValues[i];
                            this.selectedValueCategoryOptionLogicRule[index - 1];
                        }
                    });
                }
            },
        },
        selectedCategoriesCategoryOption: {
            deep: true,
            handler(newValue, oldValue) {
                for (let i in this.selectedCategoriesCategoryOption) {

                    console.log("Category changed", newValue[i])
                    this.rulesCategoryOption.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.category = newValue[i];
                            this.selectedCategoriesCategoryOption[index - 1];
                        }
                    });
                }
            }
        },
        ruleLogicCategoryOption: {
            deep: true,
            handler(newValue, oldValue) {
                for (let i in this.ruleLogicCategoryOption) {

                    console.log("Option rule changed", newValue[i])
                    this.rulesCategoryOption.logic.rules.filter((val, index) => {
                        if (index == i) {
                            val.option = newValue[i];
                            this.ruleLogicCategoryOption[index - 1];
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

        setInterval(() => {
            localStorage.removeItem('productCopyed');
            localStorage.removeItem('productID'); // pentru replacement
            localStorage.removeItem('productJSON');
            this.exportDatalocal = '';
        }, 60000)
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
        },
        // isThumbnailOptions() {
        //     if(this.selectedProductCategories) {
        //         let type = this.selectedProductCategories.filter(op => op.type);
        //         if(type == 'img' || type == 'multiple') return true;
        //         else return false; 
        //     }
        // },
        // isDropdownOptions() {
        //     if(this.selectedProductCategories) {
        //         let type = this.selectedProductCategories.filter(op => op.type);
        //         if(type == 'select') return true;
        //         else return false; 
        //     }
        // }
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
                    <WarningButton data-toggle="tooltip"  data-placement="bottom" title="Save product" @click="saveProduct">
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
                        <div class="btn-group me-2 mt-2" role="group">
                            <SuccesButton @click="createTypeOfProduct">
                                <i class="pi pi-plus" style="font-size: 1rem"></i>
                            </SuccesButton>

                            <WarningButton v-if="exportDatalocal == 'panou'" class="mx-1" @click="importElementsFunc()">
                                <i class="pi pi-plus" style="font-size: 1rem"></i>
                            </WarningButton>
                        </div>
                    </div>
                </div>

                <!-- Container Elemente -->
                <div class="mt-5">
                    <div class="bg-slate-100 rounded shadow container-sm d-flex p-2">
                        <div class="p-2 flex-grow-1">
                            <p class="p-3 h4">{{ product.data.panels?.length ? selectedProduct.title : 'No title' }}</p>
                        </div>
    
                        <div class="pt-4 px-2">
                            <i @click="exportElementsFunc(index, 'panel')" class="p-1 pi pi-share-alt hovered" data-toggle="tooltip"  data-placement="bottom" title="Export"
                            style="font-size: 1rem"></i>
                        </div>

                        <div class="pt-4 px-2">
                            <i @click="editCurrentPanel()" class="p-1 pi pi-file-edit hovered" data-toggle="tooltip"  data-placement="bottom" title="Edit"
                            style="font-size: 1rem"></i>
                        </div>
                        <div class="pt-4 px-2">
                            <i @click="cloneElementConfirm(index, 1)" class="p-1 pi pi-clone hovered"
                                data-toggle="tooltip"  data-placement="bottom" title="Clone"
                                style="font-size: 1rem"></i>
                        </div>
                        <div class="pt-4 px-2">
                            <i @click="deleteElement('panel')" class="p-1 pi pi-trash hovered alerted-hover"
                                data-toggle="tooltip"  data-placement="bottom" title="Delete"
                                style="font-size: 1rem"></i>
                        </div>
                    </div>
                </div>

                <!-- Container Content -->
                <div class="mt-5 mb-5">
                    <div class="bg-slate-100 rounded shadow container-sm p-2">
                        <div v-if="product.data.panels?.length" class="mt-2 border-bottom border-bottom">
                            <div v-for="(items, index) in selectedProductCategories" class="border-bottom border-bottom p-1">
                                <!-- div titlu + btn -->
                                <div class="d-flex mt-1">
                                    <div class="p-1 flex-grow-1">
                                        <p class="px-3 h5">{{ items.title }}</p>
                                    </div>
                                    <div class="p-1">
                                        <i @click="exportElementsFunc(index, 'category')" class="p-1 pi pi-share-alt hovered" data-toggle="tooltip"  data-placement="bottom" title="Export"
                                        style="font-size: 1rem"></i>
                                    </div>

                                    <div class="p-1">
                                        <i @click="editCurrentCategory(index)" class="p-1 pi pi-file-edit hovered"
                                            data-toggle="tooltip"  data-placement="bottom" title="Edit"
                                            style="font-size: 1rem"></i>
                                    </div>
                                    <!-- another panel copy -->
                                    <div class="p-1">
                                        <i @click="cloneElementConfirm(index, 3)" class="p-1 pi pi-copy hovered"
                                            data-toggle="tooltip"  data-placement="bottom" title="Copy"
                                            style="font-size: 1rem"></i>
                                    </div>
                                    <!-- <div class="p-1">
                                        <i class="p-1 pi pi-arrow-right-arrow-left hovered" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom" data-bs-title="Move" style="font-size: 1rem"></i>
                                    </div> -->
                                    <div class="p-1">
                                        <i @click="cloneElementConfirm(index, 2)" class="p-1 pi pi-clone hovered"
                                            data-toggle="tooltip"  data-placement="bottom" title="Clone"
                                            style="font-size: 1rem"></i>
                                    </div>
                                    <div class="p-1">
                                        <i @click="deleteElement('category', items)"
                                            class="p-1 pi pi-trash hovered alerted-hover" data-toggle="tooltip"  data-placement="bottom" title="Delete"
                                             style="font-size: 1rem"></i>
                                    </div>
                                </div>
                                <!-- div poze grid -->
                                <!-- Vor fi input + dropdown -->
                                <!-- thumb options -->
                                <div v-if="items.type == 'img' || items.type == 'multiple'" class="d-flex align-content-start flex-wrap">

                                    <div v-if="items.options?.length" class="p-4 images-layout">
                                        <div v-for="(op) in items.options" class="p-1 rounded shadow">
                                            <img data-toggle="tooltip"  data-placement="bottom" :title="`${op.option.data.label}`" style="width: 160px; height: 160px;" :src="`${op.option.data.value}`"
                                                alt="" data-bs-toggle="tooltip" data-bs-title="Order 1">
                                        </div>
                                    </div>
                                </div>

                                 <!-- thumb options -->
                                 <div v-if="items.type == 'select'" class="d-flex align-content-start flex-wrap">
                                    <div class="mx-3 m-1 font-italic">{{ items.description?.length ? items.description.replace("<p>", "").replace("</p>", "") : ''  }}</div>
                                    <select class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-100">
                                        <option selected>-</option>
                                        <option v-for="(op) in items.options" data-toggle="tooltip"  data-placement="bottom" :title="`${op.option.data.label}`" >{{ op.sku }}</option>
                                    </select>
                                 </div>

                                <!-- input options -->
                                <div v-if="items.type == 'input' || items.type == 'inputMulti' || items.type == 'text'" class="d-flex flex-wrap">
                                    <TextInput class="m-2 w-100" v-for="(op) in items.options" data-toggle="tooltip"  data-placement="bottom" :title="`${op.option.data.label}`"  :placeholder="`${op.option.data.label}`" />
                                </div>


                                <!-- edit attributes -->

                                <div class="d-flex p-4">
                                    <EditButton @click="editChoiseAttribute(items, index)">
                                        Edit attributes
                                    </EditButton>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 d-flex justify-center flex-wrap">
                            <SuccesButton @click="createNewOptionForProduct">
                                Add new custom option
                            </SuccesButton>
                        </div>
                        <div v-if="exportDatalocal == 'category'" class="d-flex justify-center flex-wrap">
                            <WarningButton @click="importElementsFunc()">
                                Import category
                            </WarningButton>
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
                                    <span v-if="index == 0" class="fs-5 p-1">If</span>
                                    <select v-if="index > 0" v-model="selectedValuesLogicRule[index]" class="logic-list m-1">
                                        <option value="||">OR</option>
                                        <option value="&&">AND</option>
                                    </select>
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
                                            <option value="any" selected>Any choice</option>
                                            <option value="oneof">One Of..</option>
                                            <option value="isnot">Not equal</option>
                                            <option
                                                v-for="op in getOptionsAfterPanelAndCategory(selectedValues[index], selectedCategories[index])"
                                                :value="op.id">{{ op.option.data.label }}</option>
                                        </select>
                                    </span>

                                    <div>
                                        <span v-if="ruleLogic[index] == 'oneof' || ruleLogic[index] == 'isnot'">
                                            <div v-for="(op, idx) in getOptionsAfterPanelAndCategoryIfChecked(selectedValues[index], selectedCategories[index], index)"
                                                class="p-2 m-2">
                                                <label class="fs-6" for="checkBox">
                                                    <input :class="`checkedPanelOp_${index}`"
                                                        @change="checkBoxOptionsPanel($event, op, index)"
                                                        :data-option="op.id" type="checkbox" name="checkbox">
                                                    {{ op.option.data.label }}
                                                </label>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div @click.stop="deleteCurrentLogic(index)"
                                    class="p-1 flex-shrink-1 btn-delete-logic border border-warning">
                                    <div class="align-btn-text">
                                        <span>
                                            <i class="p-1 pi pi-trash hovered alerted-hover" data-bs-toggle="tooltip"
                                                data-bs-placement="bottom" data-bs-title="Delete"
                                                style="font-size: 1rem"></i>
                                        </span>
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
                                <select v-model="logicVisible_panels" class="logic-list-show">
                                    <option value="">Not Selected</option>
                                    <option value="show">Shown</option>
                                    <option value="hide">Hidden</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <div class="p-2">
                                <EditButton @click="createNewProduct">
                                    APPLY
                                </EditButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- logic -->
                <!-- Apply & Cancel -->
            </Dialog>

            <!-- modal add new category -->

            <Dialog v-model:visible="createNewCategory" modal header="Add new category option" :style="{ width: '50vw' }">

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

                            <div class="p-3">
                                <InputLabel for="categoryDescription" value="Description" />

                                <TextareaInput v-model="categoryDescription" type="text" class="mt-1 block w-full" autofocus
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
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab"
                        tabindex="0">

                        <!-- logica category -->

                        <div class="container mb-3">
                            <div v-for="(value, index) in rulesCategory.logic.rules" :key="index" class="d-flex m-2">
                                <div class="p-2 w-100 p-2 m-2">
                                    <span v-if="index == 0" class="fs-5 p-1">If</span>
                                    <select v-if="index > 0" v-model="selectedValueCategoryLogicRule[index]" class="logic-list m-1">
                                        <option value="||">OR</option>
                                        <option value="&&">AND</option>
                                    </select>
                                    <select v-model="selectedValueCategory[index]" class="logic-list">
                                        <option>Select Step</option>
                                        <option v-for="op in rulesPanelPanels[0].panels" :value="op.id">{{ op.title }}
                                        </option>
                                    </select>
                                    <span v-if="getCategoriesAfterPanel(selectedValueCategory[index])?.length">
                                        <span class="fs-5 p-1">'s custom option</span>
                                        <select v-model="selectedCategoriesCategory[index]" class="logic-list">
                                            <option>Select Custom Option</option>
                                            <option v-for="op in getCategoriesAfterPanel(selectedValueCategory[index])"
                                                :value="op.id">{{ op.title }}</option>
                                        </select>
                                        <span class="fs-5 p-1">is</span>
                                        <select v-model="ruleLogicCategory[index]" class="logic-list">
                                            <option value="any" selected>Any choice</option>
                                            <option value="oneof">One Of..</option>
                                            <option value="isnot">Not equal</option>
                                            <option
                                                v-for="op in getOptionsAfterPanelAndCategory(selectedValueCategory[index], selectedCategoriesCategory[index])"
                                                :value="op.id">{{ op.option.data.label }}</option>
                                        </select>
                                    </span>

                                    <div>
                                        <span
                                            v-if="ruleLogicCategory[index] == 'oneof' || ruleLogicCategory[index] == 'isnot'">
                                            <div v-for="(op, idx) in getOptionsAfterPanelAndCategoryIfChecked(selectedValueCategory[index], selectedCategoriesCategory[index], index)"
                                                class="p-2 m-2">
                                                <label class="fs-6" for="checkBox">
                                                    <input :class="`checkedCategoryOp_${index}`"
                                                        @change="checkBoxOptionsCategory($event, op, index)"
                                                        :data-option="op.id" type="checkbox" name="checkbox">
                                                    {{ op.option.data.label }}
                                                </label>
                                            </div>

                                        </span>
                                    </div>
                                </div>

                                <div @click.stop="deleteCurrentLogicCategory(index)"
                                    class="p-1 flex-shrink-1 btn-delete-logic border border-warning">
                                    <div class="align-btn-text">
                                        <span><i class="p-1 pi pi-trash hovered alerted-hover" data-bs-toggle="tooltip"
                                                data-bs-placement="bottom" data-bs-title="Delete"
                                                style="font-size: 1rem"></i></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="p-1 d-flex justify-content-center">
                            <PrimaryButton @click="createNewRule('category')">
                                Add new rule
                            </PrimaryButton>
                        </div>

                        <div class="container mb-3">
                            <span class="fs-5 p-1">
                                then this layer should be:
                            </span>
                            <div class="p-1 d-flex justify-content-center">
                                <select v-model="logicVisible_category" class="logic-list-show">
                                    <option value="">Not Selected</option>
                                    <option value="show">Shown</option>
                                    <option value="hide">Hidden</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <div class="p-2">
                                <EditButton @click="createNewCategoryPanel">
                                    APPLY
                                </EditButton>
                            </div>
                        </div>
                    </div>
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

            <!-- dialog clone element to another panel-->
            <Dialog v-model:visible="cloneElementToPanel" modal header="Confirm message" :style="{ width: '40vw' }">
                <div class="d-flex m-3">
                    <p class="h6">
                        Choose an panel
                    </p>
                </div>
                <div class="d-flex justify-center p-3">
                    <select class="w-100" v-model="cloneElementToPanelId">
                        <option value="">Select panel</option>
                        <option v-for="element in this.product.data.panels" :value="element.id">{{ element.title }}</option>
                    </select>
                </div>
                <div class="d-flex flex-row m-3">
                    <div class="p-2">
                        <EditButton @click="cancelConfirmation()">
                            CANCEL
                        </EditButton>
                    </div>
                    <div class="p-2">
                        <EditButton @click="confirmClone()">
                            CONFIRM
                        </EditButton>
                    </div>
                </div>


            </Dialog>

            <!-- dialog clone element -->
            <Dialog v-model:visible="cloneElement" modal header="Confirm message" :style="{ width: '40vw' }">
                <div class="d-flex m-3">
                    <p class="h6">
                        Are you sure to clone this element?
                    </p>
                </div>
                <div class="d-flex flex-row m-3">
                    <div class="p-2">
                        <EditButton @click="cancelConfirmation()">
                            CANCEL
                        </EditButton>
                    </div>
                    <div class="p-2">
                        <EditButton @click="confirmClone()">
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
                                <a>{{
                                    selectedProductCategories[this.selectCurrentProductCategoryIndex].title }}</a>
                            </strong>
                        </li>
                        <!-- get title of current option -->
                        <li v-if="newOptionRequest && editOption" class="breadcrumb-item active" aria-current="page">
                            <strong>
                                <a>{{
                                    selectedProductCategories[this.selectCurrentProductCategoryIndex].options[this.editOptionID].option.data.label }} </a>
                            </strong>
                        </li>
                    </ol>
                </nav>
                <ul v-if="newOptionRequest" class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane"
                            type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Edit</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane"
                            type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Logic</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab"
                        tabindex="0">
                        <!-- Option create - false -->
                        <div v-if="!newOptionRequest">
                            <div class="d-flex">
                                <!-- <div class="p-2 flex-grow-1">
                                    <TextInput v-model="title_productCategory" placeholder="Search..." type="text"
                                        class="mt-1 block w-full" autofocus autocomplete="" />
                                </div>
                        -->
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
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="pointer-hover"
                                        v-for="(items, index) in selectedProductCategories[this.selectCurrentProductCategoryIndex].options">
                                        <td class="image-td-align">
                                            <div v-if="selectedProductCategories[this.selectCurrentProductCategoryIndex].type != 'input' && selectedProductCategories[this.selectCurrentProductCategoryIndex].type != 'inputMulti' && selectedProductCategories[this.selectCurrentProductCategoryIndex].type != 'text'" class="p-4">
                                                <div v-if="selectedProductCategories[this.selectCurrentProductCategoryIndex].type != 'select'"  class="p-1 d-flex justify-content-center">
                                                    <img style="width: 51px; height: 51px;"
                                                        :src="`${items.option.data.value}`" alt="" data-toggle="tooltip"  data-placement="bottom" :title="`${items.option.data.label}`" >
                                                </div>
                                                 <!-- drop down inputs -->
                                                <span style="text-align: center;" class="image-td-align wrapped-text">
                                                    {{ selectedProductCategories[this.selectCurrentProductCategoryIndex].type == 'select' && items.option.data.value?.length ? '( ' +items.option.data.value+ ' )' : '' }} {{ items.sku?.length ? '[ ' + items.sku + ' ]' : '' }}
                                                </span>
                                            </div>

                                            <!-- input options -->
                                            <div v-else class="p-3">
                                                <InputLabel class="p-1" :for="`${items.option.data.label}`" :value="`${items.option.data.label}`" />
                                                <TextInput :placeholder="`${items.sku}`"/>
                                            </div>
                                        </td>

                                        <td class="text-td-align">
                                            <div class="p-1">
                                                <i @click="editChosedOption(items, index)"
                                                    class="p-1 pi pi-file-edit warning-hover" data-toggle="tooltip"  data-placement="bottom" title="Edit" 
                                                    style="font-size: 1rem"></i>

                                                <i @click="deleteChosedOption(items, index)"
                                                    class="p-3 pi pi-trash alerted-hover" data-toggle="tooltip"  data-placement="bottom" title="Delete" 
                                                    style="font-size: 1rem"></i>
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

                                <TextInput v-model="optionLabel" type="text" class="mt-1 block w-full" autofocus
                                    autocomplete="" />

                            </div>

                            <div class="p-3">
                                <InputLabel for="" value="Thumbnail" />

                                <div>
                                    <div @click.stop="deleteCurrentImage(items, index)" class="trash-btn-img">
                                        <i class="p-1 pi pi-trash"></i>
                                    </div>
                                    <div id="drop-area" @dragover.prevent="handleDragOver"
                                        @dragleave.prevent="handleDragLeave" @drop.prevent="handleDrop">
                                        <p>Drag & drop a PNG, JPG, or JPEG file here or click to select one.</p>
                                        <input type="file" id="file-input" accept=".png, .jpg, .jpeg"
                                            @change="handleFileSelect" />

                                        <div class="d-flex justify-content-center">
                                            <img v-if="imagePreview" :src="imagePreview" alt="Image Preview"
                                                id="image-preview" />
                                        </div>
                                    </div>

                                    <!-- <h4 class="m-1" style="text-align: center; font-weight: bold; font-style: italic;">OR
                                    </h4>

                                    <div class="p-1 d-flex justify-content-center">
                                        <PrimaryButton class="ml-4">
                                            Select an image from gallery
                                        </PrimaryButton>
                                    </div> -->
                                </div>
                            </div>

                            <div class="p-3">
                                <InputLabel for="optionSKU" value="SKU" />

                                <TextInput v-model="optionSKU" type="text" class="mt-1 block w-full" autofocus
                                    autocomplete="" />

                            </div>

                            <div class="d-flex flex-row m-3">
                                <div class="p-2">
                                    <EditButton @click="generateNewOption()">
                                        APPLY
                                    </EditButton>
                                </div>
                                <div class="p-2">
                                    <EditButton @click="cancelNewOption()">
                                        BACK
                                    </EditButton>
                                </div>
                            </div>

                        </div>
                        <!-- logic -->
                        <!-- Apply & Cancel -->
                    </div>
                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab"
                        tabindex="0">


                        <!-- logica category option -->

                        <div class="container mb-3">
                            <div v-for="(value, index) in rulesCategoryOption.logic.rules" :key="index" class="d-flex m-2">
                                <div class="p-2 w-100 p-2 m-2">
                                    <span v-if="index == 0" class="fs-5 p-1">If</span>
                                    <select v-if="index > 0" v-model="selectedValueCategoryOptionLogicRule[index]" class="logic-list m-1">
                                        <option value="||">OR</option>
                                        <option value="&&">AND</option>
                                    </select>
                                    <select v-model="selectedValueCategoryOption[index]" class="logic-list">
                                        <option>Select Step</option>
                                        <option v-for="op in rulesPanelPanels[0].panels" :value="op.id">{{ op.title }}
                                        </option>
                                    </select>
                                    <span v-if="getCategoriesAfterPanel(selectedValueCategoryOption[index])?.length">
                                        <span class="fs-5 p-1">'s custom option</span>
                                        <select v-model="selectedCategoriesCategoryOption[index]" class="logic-list">
                                            <option>Select Custom Option</option>
                                            <option
                                                v-for="op in getCategoriesAfterPanel(selectedValueCategoryOption[index])"
                                                :value="op.id">{{ op.title }}</option>
                                        </select>
                                        <span class="fs-5 p-1">is</span>
                                        <select v-model="ruleLogicCategoryOption[index]" class="logic-list">
                                            <option value="any" selected>Any choice</option>
                                            <option value="oneof">One Of..</option>
                                            <option value="isnot">Not equal</option>
                                            <option
                                                v-for="op in getOptionsAfterPanelAndCategory(selectedValueCategoryOption[index], selectedCategoriesCategoryOption[index])"
                                                :value="op.id">{{ op.option.data.label }}</option>
                                        </select>
                                    </span>

                                    <div>
                                        <span
                                            v-if="ruleLogicCategoryOption[index] == 'oneof' || ruleLogicCategoryOption[index] == 'isnot'">
                                            <div v-for="(op, idx) in getOptionsAfterPanelAndCategoryIfChecked(selectedValueCategoryOption[index], selectedCategoriesCategoryOption[index], index)"
                                                class="p-2 m-2">
                                                <label class="fs-6" for="checkBox">
                                                    <input :class="`checkedOptionsOp_${index}`"
                                                        @change="checkBoxOptionsOption($event, op, index)"
                                                        :data-option="op.id" type="checkbox" name="checkbox">
                                                    {{ op.option.data.label }}
                                                </label>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div @click.stop="deleteCurrentLogicCategoryOption(index)"
                                    class="p-1 flex-shrink-1 btn-delete-logic border border-warning">
                                    <div class="align-btn-text">
                                        <span><i class="p-1 pi pi-trash hovered alerted-hover" data-bs-toggle="tooltip"
                                                data-bs-placement="bottom" data-bs-title="Delete"
                                                style="font-size: 1rem"></i></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="p-1 d-flex justify-content-center">
                            <PrimaryButton @click="createNewRule('option')">
                                Add new rule
                            </PrimaryButton>
                        </div>

                        <div class="container mb-3">
                            <span class="fs-5 p-1">
                                then this layer should be:
                            </span>
                            <div class="p-1 d-flex justify-content-center">
                                <select v-model="logicVisible_options" class="logic-list-show">
                                    <option value="">Not Selected</option>
                                    <option value="show">Shown</option>
                                    <option value="hide">Hidden</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <div class="p-2">
                                    <EditButton @click="generateNewOption()">
                                        APPLY
                                    </EditButton>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </Dialog>

        <!-- modal add new product -->
           <Dialog v-model:visible="exportElements" modal header="Export ( in 1 minute expires )" :style="{ width: '60vw' }">
                <code>{{ JSON.parse(exportData)  }}</code>
            </Dialog>   
        
        </div>
        <div v-if="isOverlayVisible" class="overlay"></div>
    </AuthenticatedLayout>
</template>
