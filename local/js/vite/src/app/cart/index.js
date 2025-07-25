import { createApp } from "vue";
import App from "./App.vue";
import store from "@/store/cart";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createCart(pathToOrder) {
        store.dispatch('initialize', {
            checkoutUrl: pathToOrder
        });
        const app = createApp(App).use(store);
        return app;
    }
}