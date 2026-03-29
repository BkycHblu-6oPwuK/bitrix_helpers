import { authorize } from "@/api/auth";
import { showErrorNotification } from "@/app/notify";
import { closePreloader, showPreloader } from "@/app/preloader";
import ResultError from "@/lib/ResultError";
import Validator from "@/lib/Validator"

const loginEmail = {
    namespaced: true,
    state: {
        form: {
            email: '',
            password: '',
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
        async login({ dispatch, getters }) {
            dispatch('validate')
            if (!getters.errorsIsEmpty) return;
            try {
                showPreloader();
                const result = await authorize(getters.getForm);
                if (!result.success) {
                    if (result.error) {
                        showErrorNotification(result.error)
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
                console.error('Ошибка при авторизации: ', error)
            } finally {
                closePreloader();
            }
        },
        validate({ commit, getters }) {
            commit('setErrors', {});
            const validator = new Validator();
            const rules = {
                email: {
                    condition: (value) => value && validator.validateEmail(value),
                    message: 'Введите корректный email',
                },
                password: {
                    condition: (value) => value && value.length > 5,
                    message: 'Пароль неверен',
                },
            };
            commit('setErrors', validator.validateForm(getters.getForm, rules));
        }
    },
    getters: {
        getForm: (state) => state.form,
        getErrors: (state) => state.errors,
        errorsIsEmpty: (state) => Object.values(state.errors).length === 0,
    }
};

export default loginEmail;
