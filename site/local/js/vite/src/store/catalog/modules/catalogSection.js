import { loadPage } from '@/api/catalog';
import { closePreloader, showPreloader } from '@/app/preloader';
import ResultError from '@/lib/ResultError';
import { showErrorNotification } from '@/app/notify';
import { pushInState } from '@/common/js/helpers';
import { actionLoadItems } from '@/common/js/variables';

const catalogSection = {
    namespaced: true,
    state: {
        items: [],
        pagination: {},
    },
    mutations: {
        updateItems(state, { items, append = false }) {
            state.items = append ? [...state.items, ...items] : items;
        },
        setPagination(state, pagination) {
            state.pagination = pagination;
        },
    },
    actions: {
        initialize({ commit }, data) {
            commit('updateItems', { items: data.items });
            commit('setPagination', data.pagination);
        },
        async fetchPage({ commit, state }, { page, append = false }) {
            showPreloader();
            const params = new URLSearchParams(window.location.search);
            params.set('action', actionLoadItems);
            params.set(state.pagination.paginationUrlParam, page);

            try {
                let url = new URL(window.location.href);
                url.search = params.toString();
                const result = await loadPage(url);
                pushInState(url);
                commit('updateItems', { items: result.catalogSection.items, append });
                commit('setPagination', result.catalogSection.pagination);
            } catch (error) {
                if(error instanceof ResultError){
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при загрузке страницы:', error);
            } finally {
                closePreloader();
            }
        },
        showMore({ dispatch, state }) {
            dispatch('fetchPage', { page: state.pagination.currentPage + 1, append: true });
        },
        changePage({ dispatch }, page) {
            dispatch('fetchPage', { page, append: false });
        },
    },
    getters: {
        getItems: (state) => state.items,
        getPagination: (state) => state.pagination,
    }
};

export default catalogSection;
