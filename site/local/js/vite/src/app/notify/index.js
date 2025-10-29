import { createApp } from 'vue';
import { useNotification } from '@kyvg/vue3-notification';
import Notifications from '@kyvg/vue3-notification';
import App from './App.vue';

const { notify }  = useNotification()

/**
 * @param {string} msg сообщение
 * @param {number} timeout через сколько мс. исчезнет
 */
export function showErrorNotification(msg = "Произошла непредвиденная ошибка, попробуйте позже", timeout = 2000) {
    notify({
        group: 'top',
        type: 'error',
        text: msg,
        duration: timeout
    });
}

/**
 * @param {string} msg сообщение
 * @param {number} timeout через сколько мс. исчезнет
 */
export function showSuccessNotification(msg, timeout = 2000) {
    notify({
        group: 'top',
        type: 'success',
        text: msg,
        duration: timeout
    });
}

/**
 * @param {string} msg сообщение
 * @param {number} timeout через сколько мс. исчезнет
 */
export function showInfoNotification(msg, timeout = 2000) {
    notify({
        group: 'top',
        type: 'info',
        text: msg,
        duration: timeout
    });
}

const app = createApp(App);
app.use(Notifications);
app.mount('#vue-notification');
