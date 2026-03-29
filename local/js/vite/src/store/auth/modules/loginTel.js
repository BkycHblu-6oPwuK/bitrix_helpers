import { checkCode, sendCode } from "@/api/auth";
import { showErrorNotification } from "@/app/notify";
import { closePreloader, showPreloader } from "@/app/preloader";
import ResultError from "@/lib/ResultError";
import Validator from "@/lib/Validator"

const loginTel = {
    namespaced: true,
    state: {
        form: {
            phone: '',
        },
        errors: {},
        sendCodeIsSuccess: false,
    },
    mutations: {
        setForm(state, form) {
            state.form = form;
        },
        setErrors(state, errors) {
            state.errors = errors;
        },
        setSendCodeIsSuccess(state, isSuccess){
            state.sendCodeIsSuccess = isSuccess
        }
    },
    actions: {
        async sendCode({ commit, dispatch, getters }) {
            commit('setSendCodeIsSuccess', false);
            dispatch('validate');
            if (!getters.errorsIsEmpty) return;
            try {
                showPreloader();
                const result = await sendCode(getters.getForm.phone);
                commit('setSendCodeIsSuccess', true);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                }
                console.error('Ошибка при отправке кода: ', error)
            } finally {
                closePreloader();
            }
        },
        async checkCode({getters, commit}, code){
            try {
                commit('setErrors', {});
                showPreloader();
                const result = await checkCode(getters.getForm.phone, code);
                if(!result.isVerified){
                    commit('setErrors', {
                        code: "Код введен не верно"
                    })
                } else {
                    window.location.href = result.url;
                }
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при проверке кода: ', error)
            } finally {
                closePreloader();
            }
        },
        validate({ commit, getters }) {
            commit('setErrors', {});
            const validator = new Validator();
            const rules = {
                phone: {
                    condition: (value) => validator.validatePhone(value),
                    message: 'Введите корректный номер телефона',
                },
            };
            commit('setErrors', validator.validateForm(getters.getForm, rules));
        }
    },
    getters: {
        getForm: (state) => state.form,
        getErrors: (state) => state.errors,
        errorsIsEmpty: (state) => Object.values(state.errors).length === 0,
        sendCodeIsSuccess: (state) => state.sendCodeIsSuccess
    }
};

export default loginTel;
