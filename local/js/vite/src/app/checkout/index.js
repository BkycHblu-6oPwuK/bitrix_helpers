import { computed, createApp } from "vue";
import App from "./App.vue";
import './style.css';
import store from "@/store/checkout";
import storeAbout from "@/store/about";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createCheckout(data) {
        store.dispatch('initialize', data)
        const app = createApp(App).use(store);
        app.provide('isMobile', computed(() => storeAbout.getters.isMobile));
        return app;
    }
}