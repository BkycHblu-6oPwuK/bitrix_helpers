import { createApp, ref } from 'vue';
import loader from "vue3-ui-preloader";
import "vue3-ui-preloader/dist/loader.css"

const isLoading = ref(false);

export function showPreloader() {
    isLoading.value = true;
}

export function closePreloader() {
    isLoading.value = false;
}

const app = createApp({
    setup() {
        return { isLoading };
    },
    template: `<loader v-if="isLoading" name="dots" loadingText="LOADING..." textColor="#ffffff" textSize="15" textWeight="800" object="#000000" color1="#ffffff" color2="#17fd3d" size="5" speed="2" bg="#343a40" objectbg="#999793" opacity="80" :disableScrolling="false" />`
});

app.component('loader', loader);
app.mount('#vue-preloader'); // подключается в футере
