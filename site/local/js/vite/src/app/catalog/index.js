import { createApp } from "vue";
import store from '/src/store/catalog/index.js';
import App from './App.vue';

window.vueApps = {
    ...(window.vueApps ?? {}),
    createCatalog(data) {
        store.dispatch('catalogFilter/initialize', data.filter);
        store.dispatch('catalogSectionList/initialize', data.catalogSectionList);
        store.dispatch('catalogSection/initialize', data.catalogSection);
        const app = createApp(App).use(store)
        return app;
    }
}