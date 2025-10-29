import { createApp } from "vue";
import store from '/src/store/catalog_element/index.js';
import App from './App.vue';

window.vueApps = {
    ...(window.vueApps ?? {}),
    createCatalogElement(data) {
        store.dispatch('initialize', data);
        const app = createApp(App).use(store);
        return app;
    }
}