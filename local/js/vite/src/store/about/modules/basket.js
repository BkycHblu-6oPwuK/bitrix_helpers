import { addBasket, getBasket } from '@/api/cart';
import { showErrorNotification } from '@/app/notify';
import ResultError from '@/lib/ResultError';

const basketModule = {
    namespaced: true,
    state: {
        count: {},
    },
    mutations: {
        setCount(state, count) {
            state.count = count;
        },
    },
    actions: {
        async initialize({ commit }) {
            try {
                const result = await getBasket(false);
                commit('setCount', result.summary.totalQuantity);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при загрузке корзины:', error);
            } 
        },
        async addBasket({ commit }, productId ) {
            try {
                const result = await addBasket(productId, false)
                commit('setCount', result.summary.totalQuantity);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при добавлении товара в корзину:', error);
            }
        },
    },
    getters: {
        getCount: (state) => state.count,
    },
};

export default basketModule;
