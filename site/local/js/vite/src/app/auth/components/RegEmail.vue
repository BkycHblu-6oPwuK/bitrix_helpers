<script setup>
import { computed, ref, useTemplateRef } from 'vue';
import { useStore } from 'vuex';
import { vMaska } from "maska/vue"
import { phoneMask } from '@/common/js/variables';

const store = useStore();
const emits = defineEmits(['close', 'swithToLoginEmail']);
const popupForm = useTemplateRef('popupForm');

const form = computed(() => store.getters['regEmail/getForm']);
const errors = computed(() => store.getters['regEmail/getErrors']);

const passwordIsHidden = ref(true);
const passwordConfirmIsHidden = ref(true);

const submit = () => {
    store.dispatch('regEmail/register');
}

defineExpose({
    popupForm
});
</script>

<template>
    <form class="reg-popup__form popup-form hidden" ref="popupForm">
        <a href="index.php" class="return-btn-mobile">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M10 12L6 8L10 4" stroke="#6B7280" stroke-width="1.5" stroke-linecap="square" />
            </svg>
            <span>Вернуться на главную</span>
        </a>
        <span class="reg-popup__form-title">Регистрация в Dzhavadoff</span>
        <label for="reg-name" class="form-input-label">Ваше имя</label>
        <div class="reg-popup__form-name form-input-block" :class="{
            'input-error': errors.name
        }">
            <input id="reg-name" type="text" v-model="form.name" class="form-input input" placeholder="Алексей">
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
        <span class="login-popup__form-req-name-err-msg error-message" v-if="errors.name">{{ errors.name }}</span>
        <label for="reg-email" class="form-input-label">E-mail</label>
        <div class="reg-popup__form-email form-input-block" :class="{
            'input-error': errors.email
        }">
            <input id="reg-email" type="text" v-model="form.email" class="form-input input" placeholder="alexey@ya.ru">
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
        <span class="login-popup__form-reg-email-err-msg error-message" v-if="errors.email">{{ errors.email }}</span>
        <label for="reg-tel" class="form-input-label">Телефон</label>
        <div class="reg-popup__form-phone form-input-block" :class="{
            'input-error': errors.phone
        }">
            <input id="reg-tel" type="text" v-model="form.phone" v-maska="phoneMask" class="form-input input"
                placeholder="+7 900 800 80 80">
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
        <span class="login-popup__form-reg-phone-err-msg error-message" v-if="errors.phone">{{ errors.phone }}</span>
        <label for="reg-password" class="form-input-label">Пароль</label>
        <div class="reg-popup__form-password form-input-block" :class="{
            'input-error': errors.password
        }">
            <input id="reg-password" :type="passwordIsHidden ? 'password' : 'text'" v-model="form.password"
                class="form-input input" placeholder="• • • • • • • • • • • • • • • •">
            <div class="register-popup__form-eye_opened" v-if="passwordIsHidden"
                @click="passwordIsHidden = !passwordIsHidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="#9CA3AF"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path
                        d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                        stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="register-popup__form-eye_closed" v-else @click="passwordIsHidden = !passwordIsHidden">
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
        <span class="login-popup__form-reg-password-err-msg error-message" v-if="errors.password">{{ errors.password
            }}</span>
        <label for="reg-password-repeat" class="form-input-label">Повторите пароль</label>
        <div class="reg-popup__form-password-repeat form-input-block" :class="{
            'input-error': errors.password_confirm
        }">
            <input id="reg-password-repeat" :type="passwordConfirmIsHidden ? 'password' : 'text'"
                v-model="form.password_confirm" class="form-input input" placeholder="• • • • • • • • • • • • • • • •">
            <div class="register-popup__form-eye_opened-rep" v-if="passwordConfirmIsHidden"
                @click="passwordConfirmIsHidden = !passwordConfirmIsHidden">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="#9CA3AF"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path
                        d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                        stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="register-popup__form-eye_closed-rep" v-else
                @click="passwordConfirmIsHidden = !passwordConfirmIsHidden">
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
        <span class="reg-popup__form-password-rep-err-msg error-message" v-if="errors.password_confirm">{{
            errors.password_confirm }}</span>
        <button type="submit" class="reg-popup__form-submit" @click.prevent="submit">Зарегистрироваться</button>
        <button type="button" class="reg-popup__form-login" @click="emits('swithToLoginEmail')">Войти в аккаунт</button>
        <div class="login-popup__form-close" @click="emits('close')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M18.75 5.25L5.25 18.75" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M18.75 18.75L5.25 5.25" stroke="#D1D5DB" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </form>
</template>