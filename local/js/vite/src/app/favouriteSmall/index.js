import { createApp } from "vue";
import App from "./App.vue";
// создадим приложение для кнопки избранное, для тех карточек, что не на vue
window.vueApps = {
    ...(window.vueApps ?? {}),
    createFavouriteSmall(productId) {
        const app = createApp(App);
        app.provide('productId', productId)
        return app;
    }
};
