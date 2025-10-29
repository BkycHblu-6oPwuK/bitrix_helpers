<script setup>
import { computed, ref, useTemplateRef } from 'vue';
import { useStore } from 'vuex';

const emits = defineEmits(['close', 'swithToRegEmail', 'swithToLoginTel']);
const popupForm = useTemplateRef('popupForm');

const store = useStore();
const form = computed(() => store.getters['loginEmail/getForm']);
const errors = computed(() => store.getters['loginEmail/getErrors']);

const passwordIsHidden = ref(true);

const submit = () => {
    store.dispatch('loginEmail/login');
}

defineExpose({
    popupForm
});
</script>

<template>
    <form class="login-popup__form popup-form login-popup__form-by-email hidden" ref="popupForm">
        <a href="/" class="return-btn-mobile">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M10 12L6 8L10 4" stroke="#6B7280" stroke-width="1.5" stroke-linecap="square" />
            </svg>
            <span>Вернуться на главную</span>
        </a>
        <span class="login-popup__form-title">Вход в Dzhavadoff</span>
        <label for="email" class="form-input-label">E-mail</label>
        <div class="login-popup__form-email form-input-block" :class="{
            'input-error': errors.email
        }">
            <input id="email" type="text" v-model="form.email" placeholder="alexey@mail.ru" class="form-input input">
            <div class="login-popup__form-ok-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                        stroke-linecap="square" />
                </svg>
            </div>
            <div class="login-popup__form-err-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                    <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                </svg>
            </div>
        </div>
        <span class="login-popup__form-email-err-msg error-message" v-if="errors.email">{{ errors.email }}</span>
        <label for="password" class="form-input-label">Пароль</label>
        <div class="login-popup__form-password form-input-block" :class="{
            'input-error': errors.password
        }">
            <input id="password" :type="passwordIsHidden ? 'password' : 'text'" v-model="form.password"
                class="form-input input" placeholder="• • • • • • • • • • • • • • • •">
            <div class="login-popup__form-eye_opened" v-if="passwordIsHidden"
                @click="passwordIsHidden = !passwordIsHidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="#9CA3AF"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path
                        d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                        stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="login-popup__form-eye_closed" v-else @click="passwordIsHidden = !passwordIsHidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="11" viewBox="0 0 21 11" fill="none">
                    <path d="M17.8577 3.93481L19.9961 7.63868" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M13.4546 5.99341L14.1215 9.77565" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M8.53714 5.99158L7.87012 9.77444" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M4.13823 3.93176L1.9895 7.65347" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path
                        d="M1.99976 1.83203C3.57593 3.78301 6.46545 6.24999 10.9998 6.24999C15.5341 6.24999 18.4237 3.78303 19.9998 1.83205"
                        stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="login-popup__form-ok-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M3.33325 8.66663L5.99992 11.3333L12.6666 4.66663" stroke="#219653" stroke-width="2"
                        stroke-linecap="square" />
                </svg>
            </div>
            <div class="login-popup__form-err-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M12 4L4 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                    <path d="M4 4L12 12" stroke="#F42057" stroke-width="2" stroke-linecap="square" />
                </svg>
            </div>
        </div>
        <span class="login-popup__form-password-err-msg error-message" v-if="errors.password">{{ errors.password
        }}</span>
        <a href="#" class="login-popup__form-link">Забыли пароль?</a>
        <button type="submit" class="login-popup__form-submit" @click.prevent="submit">Войти</button>
        <button type="button" class="login-popup__form-switch switch-to-tel" @click="emits('swithToLoginTel')">Войти
            через телефон</button>
        <button type="button" class="login-popup__form-register" @click="emits('swithToRegEmail')">Регистрация</button>
        <div class="login-popup__form-close" @click="emits('close')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18.75 5.25L5.25 18.75" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M18.75 18.75L5.25 5.25" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </form>
</template>