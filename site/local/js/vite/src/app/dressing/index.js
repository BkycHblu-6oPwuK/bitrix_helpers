import { createApp } from "vue";
import App from "./App.vue";
import store from "@/store/dressing";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createDressing() {
        store.dispatch('initialize');
        const app = createApp(App).use(store);
        return app;
    }
}