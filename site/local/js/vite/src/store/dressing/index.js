import { createDressingOrder, getDressing, toggleDressing } from '@/api/catalog';
import { closePreloader, showPreloader } from '@/app/preloader';
import { createStore } from 'vuex';
import Validator from '@/lib/Validator';
import { showErrorNotification } from '@/app/notify';
import ResultError from '@/lib/ResultError';

const store = createStore({
    state: {
        items: {},
        summary: {},
        form: {
            name: '',
            phone: '',
        },
        errors: {}
    },
    mutations: {
        setItems(state, items) {
            state.items = items;
        },
        setSummary(state, summary) {
            state.summary = summary;
        },
        setForm(state, form) {
            state.form = form;
        },
        setErrors(state, errors) {
            state.errors = errors;
        }
    },
    actions: {
        async initialize({ dispatch }) {
            try {
                showPreloader();
                const result = await getDressing();
                dispatch('setData', result);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при загрузке примерочной:', error);
            } finally {
                closePreloader();
            }
        },
        async toggleDressing({ dispatch }, offerId) {
            try {
                const result = await toggleDressing(offerId);
                dispatch('setData', result);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при добавлении товара в примерочную:', error);
            }
        },
        async createOrder({ getters, dispatch }) {
            dispatch('validate');
            if (!getters.errorsIsEmpty) {
                return;
            }
            try {
                const formData = new URLSearchParams({
                    form: JSON.stringify(getters.getForm),
                });
                const result = await createDressingOrder(formData);
                if (result.redirectUrl) {
                    window.location.href = result.redirectUrl
                }
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error(error);
            }
        },
        validate({ getters, commit }) {
            commit('setErrors', {});
            const validator = new Validator();
            const rules = {
                name: {
                    condition: (value) => !!value,
                    message: 'Введите Ваше имя',
                },
                phone: {
                    condition: (value) => validator.validatePhone(value),
                    message: 'Введите Ваш номер телефона',
                },
            };
            commit('setErrors', validator.validateForm(getters.getForm, rules));
        },
        setData({ commit }, data) {
            commit('setItems', data.items);
            commit('setSummary', data.summary);
            commit('setForm', data.form);
        }
    },
    getters: {
        getItems: (state) => state.items,
        getSummary: (state) => state.summary,
        getForm: (state) => state.form,
        getErrors: (state) => state.errors,
        errorsIsEmpty: (state) => Object.values(state.errors).length === 0,
    },
});

export default store;