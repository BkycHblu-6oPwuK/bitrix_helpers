import { createApp } from "vue";
import store from '/src/store/articles/index.js';
import App from './App.vue';

window.vueApps = {
    ...(window.vueApps ?? {}),
    createArticles(data) {
        store.dispatch('initialize', data.articlesList);
        const app = createApp(App).use(store)
        return app;
    }
}