import { getDressing } from '@/api/profile';
import ResultError from '@/lib/ResultError';
import { showErrorNotification } from '@/app/notify';
import { closePreloader, showPreloader } from '@/app/preloader';

const dressing = {
    namespaced: true,
    state: {
        isInitialize: false,
        orders: [],
        pagination: {}
    },
    mutations: {
        setIsInitialize(state) {
            state.isInitialize = true;
        },
        setOrders(state, orders) {
            state.orders = orders;
        },
        setPagination(state, pagination) {
            state.pagination = pagination;
        },
        setCurrentPage(state, page) {
            state.pagination.currentPage = page
        },
        setOrderItem(state, { id, item }) {
            const index = state.orders.findIndex(order => order.id === id);
            if (index !== -1) {
                state.orders[index] = item;
            }
        }
    },
    actions: {
        async initialize({ getters, commit }) {
            if (!getters.isInitialize) {
                try {
                    showPreloader();
                    const result = await getDressing();
                    commit('setOrders', result.data.orders);
                    commit('setPagination', result.data.pagination);
                    commit('setIsInitialize');
                } catch (error) {
                    if (error instanceof ResultError) {
                        showErrorNotification(error.message);
                    } else {
                        showErrorNotification();
                    }
                    console.error('Ошибка при загрузке примерочных заказов: ', error)
                } finally {
                    closePreloader();
                }
            }
        },
        async showMore({ getters, commit }) {
            let page = getters.getPagination.currentPage;
            const pageCount = getters.getPagination.pageCount;
            if (page >= pageCount) return;
            page = page + 1;
            commit('setCurrentPage', page);
            try {
                showPreloader();
                const result = await getDressing(page);
                commit('setOrders', [
                    ...getters.getOrders,
                    ...result.data.orders
                ]);
            } catch (error) {
                if (error instanceof ResultError) {
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при загрузке примерочных заказов: ', error)
            } finally {
                closePreloader();
            }
        }
    },
    getters: {
        isInitialize: (state) => state.isInitialize,
        getOrders: (state) => state.orders,
        getPagination: (state) => state.pagination,
        getOrderById: (state) => (id) => {
            return state.orders.find(order => order.id == id);
        }
    }
};

export default dressing;
