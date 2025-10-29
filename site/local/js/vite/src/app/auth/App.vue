<script setup>
import { useStore } from 'vuex';
import LoginEmail from './components/LoginEmail.vue';
import LoginTel from './components/LoginTel.vue';
import LoginTelCode from './components/LoginTelCode.vue';
import RegEmail from './components/RegEmail.vue';
import RegSuccess from './components/RegSuccess.vue';
import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { mobileMenu } from '@/common/js/variables';
import { startCountdown } from '@/common/js/helpers';
import storeAbout from '@/store/about';
import ProfileMobileMenu from './components/ProfileMobileMenu.vue';

const store = useStore();
const params = store.getters['about/getParams'];
const headerAuth = useTemplateRef('headerAuth');
const loginPopup = useTemplateRef('loginPopup');
const isMobile = computed(() => storeAbout.getters.isMobile);

const popups = {
    loginEmail: useTemplateRef('loginEmail'),
    loginTel: useTemplateRef('loginTel'),
    loginTelCode: useTemplateRef('loginTelCode'),
    regEmail: useTemplateRef('regEmail'),
    regSuccess: useTemplateRef('regSuccess'),
}

const popupForms = ref([]);

onMounted(() => {
    if (!params.isAuth) {
        for (let key in popups) {
            const popup = popups[key];
            popupForms.value.push(popup.value.popupForm);
        }
    }
})

const clickLink = () => {
    if (params.isAuth) {
        window.location.href = params.profilePageUrl;
    } else if (headerAuth.value) {

    }
}

const mobileMenuAuth = () => {
    mobileMenu.querySelector('.m-header__menu-container').style.transform = 'translateX(-254px)'
}

const clickRegBtn = () => {

}

const clickAuthBtn = () => {

}

const closeModalEmit = () => null;

const swithToLoginTel = () => null;
const swithToRegEmail = () => null;
const swithToLoginEmail = () => null;
const swithToLoginTelCode = () => {
    if (popups.loginTelCode.value.countDownBtn) {
        startCountdown(popups.loginTelCode.value.countDownBtn)
    }
};
</script>

<template>
    <div>
        <a v-if="!isMobile" class="header__main-profile" :class="{
            'auth__mode': params.isAuth
        }" @click.prevent="clickLink">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                <path
                    d="M5.8335 24.5C5.8335 19.9897 9.48984 16.3333 14.0002 16.3333C18.5105 16.3333 22.1668 19.9897 22.1668 24.5M18.6668 8.16667C18.6668 10.744 16.5775 12.8333 14.0002 12.8333C11.4228 12.8333 9.3335 10.744 9.3335 8.16667C9.3335 5.58934 11.4228 3.5 14.0002 3.5C16.5775 3.5 18.6668 5.58934 18.6668 8.16667Z"
                    stroke="#0F1523" stroke-width="2" stroke-linecap="round" />
            </svg>
        </a>
        <teleport to='#m-header-profile'>
            <div v-if="!params.isAuth" class="m-header__menu-auth-btn" @click="mobileMenuAuth">
                <span>Вход / Регистрация</span>
            </div>
            <ProfileMobileMenu v-else-if="params.isAuth && isMobile"></ProfileMobileMenu>
        </teleport>

        <teleport v-if="!params.isAuth" to='#modal-container-header'>
            <div ref="headerAuth" class="header__auth header-modal">
                <span class="header__auth-title">Вход / Регистрация</span>
                <span class="header__auth-text">Войдите или зарегистрируйтесь, чтобы оформлять заказы и получать
                    персональные предложения</span>
                <button class="header__auth-reg-btn" @click="clickRegBtn">Зарегистрироваться</button>
                <button class="header__auth-login-btn" @click="clickAuthBtn">Войти в аккаунт</button>
            </div>
        </teleport>
        <teleport v-if="!params.isAuth" to='body'>
            <div class="login-popup form-modal" ref="loginPopup">
                <LoginEmail ref="loginEmail" @close="closeModalEmit" @swithToLoginTel="swithToLoginTel"
                    @swithToRegEmail="swithToRegEmail"></LoginEmail>
                <LoginTel ref="loginTel" @close="closeModalEmit" @swithToLoginEmail="swithToLoginEmail"
                    @swithToRegEmail="swithToRegEmail" @swithToLoginTelCode="swithToLoginTelCode"></LoginTel>
                <LoginTelCode ref="loginTelCode" @close="closeModalEmit"></LoginTelCode>
                <RegEmail ref="regEmail" @close="closeModalEmit" @swithToLoginEmail="swithToLoginEmail"></RegEmail>
                <RegSuccess ref="regSuccess" @close="closeModalEmit"></RegSuccess>
            </div>
        </teleport>
    </div>
</template>