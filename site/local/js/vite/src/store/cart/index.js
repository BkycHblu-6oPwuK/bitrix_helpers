import { closePreloader, showPreloader } from '@/app/preloader';
import { addCoupon, getBasket, removeBasketItem } from '@/api/cart';
import { showErrorNotification } from '@/app/notify'
import { createStore } from 'vuex';
import ResultError from '@/lib/ResultError';

const store = createStore({
    state: {
        items: {},
        coupon: '',
        summary: {},
        params: {
            checkoutUrl: '',
        }
    },
    mutations: {
        setItems(state, items) {
            state.items = items;
        },
        setCoupon(state, coupon) {
            state.coupon = coupon;
        },
        setSummary(state, summary) {
            state.summary = summary;
        },
        setCheckoutUrl(state, url) {
            state.checkoutUrl = url;
        },
        setParams(state, params) {
            state.params = params;
        }
    },
    actions: {
        async initialize({ commit, dispatch }, params) {
            try {
                showPreloader();
                commit('setParams', params);
                const result = await getBasket(true);
                dispatch('setBasketData', result);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при загрузке корзины:', error);
            } finally {
                closePreloader();
            }
        },
        // async removeOneBasketItem({ commit }, productId) {
        //     try {
        //         const result = await removeOneBasketItem(productId, true)
        //         commit('setItems', result.items);
        //         commit('setSummary', result.summary);
        //     } catch (error) { }
        // },
        async removeBasketItem({ dispatch }, productId) {
            try {
                showPreloader()
                const result = await removeBasketItem(productId, true)
                dispatch('setBasketData', result);
            } catch (error) { 
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при удалении товара из корзины:', error);
            } finally {
                closePreloader();
            }
        },
        // async addBasket({ commit }, productId) {
        //     try {
        //         const result = await addBasket(productId, true)
        //         commit('setItems', result.items);
        //         commit('setSummary', result.summary);
        //     } catch (error) { }
        // },
        async addCoupon({ dispatch }, couponCode) {
            try {
                showPreloader();
                const result = await addCoupon(couponCode);
                dispatch('setBasketData', result);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при добавлении купона:', error);
            } finally {
                closePreloader();
            }
        },
        setBasketData({ commit }, data) {
            commit('setItems', data.items);
            commit('setCoupon', data.coupon);
            commit('setSummary', data.summary);
        }
    },
    getters: {
        getItems: (state) => state.items,
        getCoupon: (state) => state.coupon,
        getSummary: (state) => state.summary,
        getCheckoutUrl: (state) => state.params.checkoutUrl,
        getParams: (state) => state.params,
    },
});

export default store;
