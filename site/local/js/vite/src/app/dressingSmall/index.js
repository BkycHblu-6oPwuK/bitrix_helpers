import { createApp } from "vue";
import App from "./App.vue";
// создадим приложение для кнопки в примерочную, для тех карточек, что не на vue
window.vueApps = {
    ...(window.vueApps ?? {}),
    createDressingSmall(offerId) {
        const app = createApp(App);
        app.provide('offerId', offerId)
        return app;
    }
};
