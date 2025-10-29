<script setup>
import { ref, computed, useTemplateRef } from 'vue';
import { vMaska } from 'maska/vue';
import { useStore } from 'vuex';

const emits = defineEmits(['close']);
const store = useStore();
const phone = computed(() => store.getters['loginTel/getForm'].phone);
const errors = computed(() => store.getters['loginTel/getErrors']);

const code = ref(['', '', '', '']);
const inputs = useTemplateRef('inputs');
const popupForm = useTemplateRef('popupForm');
const countDownBtn = useTemplateRef('countDownBtn');
const isSubmitting = ref(false);

const focusNext = (index) => {
    if (code.value[index] && index < inputs.value.length - 1) {
        inputs.value[index + 1]?.focus();
    }
};

const handleInput = (index) => {
    if (code.value[index].length === 1) {
        inputs.value[index]?.classList.add('tel-code_active');
        focusNext(index);
    }
    checkAndSubmit();
};

const checkAndSubmit = () => {
    if (code.value.every(num => num.length === 1)) {
        submit();
    }
};

const submit = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    await store.dispatch('loginTel/checkCode', code.value.join(''));

    isSubmitting.value = false;
};

const sendCode = () => {
    store.dispatch('loginTel/sendCode');
}

defineExpose({
    popupForm,
    countDownBtn
});
</script>

<template>
    <form class="login-popup__form popup-form login-popup__form-by-tel-code hidden" ref="popupForm">
        <a href="/" class="return-btn-mobile">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M10 12L6 8L10 4" stroke="#6B7280" stroke-width="1.5" stroke-linecap="square" />
            </svg>
            <span>Вернуться на главную</span>
        </a>
        <span class="login-popup__form-title">Вход в Dzhavadoff</span>
        <label for="tel-code1" class="input-code-label form-input-label">Мы выслали код на номер {{ phone }}</label>
        <div class="login-popup__form-phone-code input">
            <input v-for="(digit, index) in code" :key="index" v-model="code[index]" v-maska="'#'"
                   class="tel-code" ref="inputs"
                   @input="handleInput(index)">
        </div>
        <span class="login-popup__form-code-err-msg error-message" v-if="errors.code">{{ errors.code }}</span>
        <button type="submit" class="login-popup__form-submit submit-by-tel-code" ref="countDownBtn" disabled @click.prevent="sendCode">Отправить код повторно</button>
        <button type="button" class="login-popup__form-switch switch-to-email">Войти через e-mail</button>
        <button type="button" class="login-popup__form-register">Регистрация</button>
        <div class="login-popup__form-close" @click="emits('close')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18.75 5.25L5.25 18.75" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M18.75 18.75L5.25 5.25" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </form>
</template>
