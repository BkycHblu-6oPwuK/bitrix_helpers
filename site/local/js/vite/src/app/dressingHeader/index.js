import { createApp } from "vue";
import App from "./App.vue";
import storeAbout from "@/store/about";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createDressingHeader(pathToDressing) {
        storeAbout.dispatch('dressing/initialize');
        const app = createApp(App);
        app.provide('pathToDressing', pathToDressing);
        return app;
    }
}