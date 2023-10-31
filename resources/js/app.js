import './bootstrap';
import '../css/app.css';

import axios from 'axios';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import PrimeVue from 'primevue/config';

const appName = import.meta.env.VITE_APP_NAME || 'AIT';
// // Define components PrimeVue
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import InputText from 'primevue/inputtext';
import Rating from 'primevue/rating';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Card from 'primevue/card';

import "primevue/resources/themes/lara-light-teal/theme.css";
import "primevue/resources/primevue.min.css"; /* Deprecated */
import "primeicons/primeicons.css";
import 'primeflex/primeflex.css'


createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            // primevue components defined
            .use(PrimeVue)
            .use(ToastService)
            .component("Calendar", Calendar)
            .component("Column", Column)
            .component("DataTable", DataTable)
            .component("Tag", Tag)
            .component("InputText", InputText)
            .component("Toast", Toast)
            .component("Button", Button)
            .component("Rating", Rating)
            .component("Dialog", Dialog)
            .component("Card", Card)
            .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });
    