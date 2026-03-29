import { createApp } from "vue";
import App from "./App.vue";
import store from "@/store/auth";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createAuth(params) {
        store.dispatch('about/initialize', params)
        const app = createApp(App).use(store);
        return app;
    }
}