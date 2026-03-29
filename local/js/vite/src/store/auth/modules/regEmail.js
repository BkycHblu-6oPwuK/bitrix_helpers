import { register } from "@/api/auth";
import { showErrorNotification } from "@/app/notify";
import { closePreloader, showPreloader } from "@/app/preloader";
import ResultError from "@/lib/ResultError";
import Validator from "@/lib/Validator"

const regEmail = {
    namespaced: true,
    state: {
        form: {
            name: '',
            email: '',
            phone: '',
            password: '',
            password_confirm: ''
        },
        errors: {}
    },
    mutations: {
        setForm(state, form) {
            state.form = form;
        },
        setErrors(state, errors) {
            state.errors = errors;
        },
    },
    actions: {
        async register({ commit, dispatch, getters }) {
            dispatch('validate')
            if (!getters.errorsIsEmpty) return;
            try {
                showPreloader();
                const result = await register(getters.getForm);
                if (!result.success) {
                    if (result.errors) {
                        commit('setErrors', result.errors)
                    } else {
                        showErrorNotification()
                    }
                } else {
                    window.location.href = result.url;
                }
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при регистрации: ', error)
            } finally {
                closePreloader();
            }
        },
        validate({ commit, getters }) {
            commit('setErrors', {});
            const validator = new Validator();
            const form = getters.getForm;
            const rules = {
                name: {
                    condition: (value) => !!value,
                    message: 'Введите Ваше имя',
                },
                email: {
                    condition: (value) => value && validator.validateEmail(value),
                    message: 'Введите корректный email',
                },
                phone: {
                    condition: (value) => validator.validatePhone(value),
                    message: 'Введите Ваш номер телефона',
                },
                password: {
                    condition: (value) => value && value.length > 5,
                    message: 'Пароль неверен',
                },
                password_confirm: {
                    condition: (value) => form.password === form.password_confirm,
                    message: 'Пароли не совпадают',
                }
            };
            commit('setErrors', validator.validateForm(form, rules));
        }
    },
    getters: {
        getForm: (state) => state.form,
        getErrors: (state) => state.errors,
        errorsIsEmpty: (state) => Object.values(state.errors).length === 0,
    }
};

export default regEmail;
