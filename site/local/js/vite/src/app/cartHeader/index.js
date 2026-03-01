import { createApp } from "vue";
import App from "./App.vue";
import storeAbout from "@/store/about";
//корзина в шапке
window.vueApps = {
    ...(window.vueApps ?? {}),
    createCartHeader(pathToBasket) {
        storeAbout.dispatch('basket/initialize');
        const app = createApp(App);
        app.provide('pathToBasket', pathToBasket);
        return app;
    }
}