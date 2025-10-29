import { createApp } from "vue";
import App from "./App.vue";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createFavouriteSmallHeader(pathToFavourites) {
        const app = createApp(App);
        app.provide('pathToFavourites', pathToFavourites);
        return app;
    }
}