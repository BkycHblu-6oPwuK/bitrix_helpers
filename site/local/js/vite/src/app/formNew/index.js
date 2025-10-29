import { createApp } from "vue";
import App from "./App.vue";
import { createAppStore } from "@/store/formNew";

window.vueApps = {
    ...(window.vueApps ?? {}),
    createFormNew(data) {
        if (!data) return null;
        const store = createAppStore(data);
        const app = createApp(App).use(store);
        return app;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.vue-form').forEach((element) => {
        try {
            const vueData = element.dataset.vueData;
            if (vueData) {
                window.vueApps.createFormNew(JSON.parse(vueData)).mount(element);
            }
        } catch (error) {
            console.error(error);
        }
    })
})