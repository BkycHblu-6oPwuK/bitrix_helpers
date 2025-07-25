import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "@/store/profile";
import { showPreloader } from "../preloader";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createProfile() {
        showPreloader(); // выключается в store после инициализации
        const app = createApp(App).use(router).use(store);
        return app;
    }
}