import { createApp } from "vue";
import App from "./App.vue";
//поиск в шапке
window.vueApps = {
    ...(window.vueApps ?? {}),
    createSearchHeader() {
        return createApp(App);
    }
}