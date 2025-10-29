import { getFavouriteIds, toggleFavourite } from '@/api/catalog';
import { showErrorNotification } from '@/app/notify';
import ResultError from '@/lib/ResultError';

const favouriteModule = {
    namespaced: true,
    state: {
        favourite: [],
        isInitialize: false
    },
    mutations: {
        setFavourite(state, favourite) {
            state.favourite = favourite;
        },
        addFavourite(state, productId) {
            state.favourite = [...state.favourite, productId];
        },
        removeFavourite(state, productId) {
            state.favourite = state.favourite.filter((item) => item !== productId);
        },
    },
    actions: {
        async initialize({ commit, state }) {
            if (!state.isInitialize) {
                try {
                    const result = await getFavouriteIds();
                    commit('setFavourite', result.data);
                    state.isInitialize = true;
                } catch (error) {
                    if(error instanceof ResultError){
                        showErrorNotification(error.message);
                    } else {
                        showErrorNotification();
                    }
                    console.error('Ошибка при загрузке избранного:', error);
                }
            }
        },
        async toggleFavourite({ commit }, productId) {
            try {
                const result = await toggleFavourite(productId);
                if (result.data.action === 'add') {
                    commit('addFavourite', productId);
                } else {
                    commit('removeFavourite', productId);
                }
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при добавлении товара в избранное:', error);
            }
        },
    },
    getters: {
        getFavourite: (state) => state.favourite,
        getFavouriteCount: (state) => state.favourite.length,
    },
};

export default favouriteModule;
