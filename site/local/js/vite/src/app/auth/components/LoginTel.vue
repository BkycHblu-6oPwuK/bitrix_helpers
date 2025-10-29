<script setup>
import { computed, useTemplateRef } from 'vue';
import { useStore } from 'vuex';
import { vMaska } from "maska/vue"
import { phoneMask } from '@/common/js/variables';

const emits = defineEmits(['close', 'swithToLoginEmail', 'swithToRegEmail', 'swithToLoginTelCode']);
const popupForm = useTemplateRef('popupForm');

const store = useStore();
const form = computed(() => store.getters['loginTel/getForm']);
const errors = computed(() => store.getters['loginTel/getErrors']);
const sendCodeIsSuccess = computed(() => store.getters['loginTel/sendCodeIsSuccess'])

const submit = async () => {
    await store.dispatch('loginTel/sendCode');
    if(!sendCodeIsSuccess.value) return;
    emits('swithToLoginTelCode');
}

defineExpose({
    popupForm
});
</script>

<template>
    <form class="login-popup__form popup-form login-popup__form-by-tel hidden" ref="popupForm">
        <a href="/" class="return-btn-mobile">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M10 12L6 8L10 4" stroke="#6B7280" stroke-width="1.5" stroke-linecap="square" />
            </svg>
            <span>Вернуться на главную</span>
        </a>
        <span class="login-popup__form-title">Вход в Dzhavadoff</span>
        <label for="tel" class="input-tel-label form-input-label">Телефон</label>
        <div class="login-popup__form-phone form-input-block" :class="{
            'input-error': errors.phone
        }">
            <input id="tel" type="text" v-model="form.phone" v-maska="phoneMask" class="form-input input" placeholder="+7 (900) 000 00 00">
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
        <span class="login-popup__form-phone-err-msg error-message" v-if="errors.phone">{{ errors.phone }}</span>
        <button type="submit" class="login-popup__form-submit submit-by-tel" @click.prevent="submit">Войти</button>
        <button type="button" class="login-popup__form-switch switch-to-email" @click="emits('swithToLoginEmail')">Войти через e-mail</button>
        <button type="button" class="login-popup__form-register" @click="emits('swithToRegEmail')">Регистрация</button>
        <div class="login-popup__form-close" @click="emits('close')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18.75 5.25L5.25 18.75" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M18.75 18.75L5.25 5.25" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </form>
</template>