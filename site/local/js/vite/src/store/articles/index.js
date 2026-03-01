import { createStore } from 'vuex';
import {closePreloader, showPreloader} from "@/app/preloader/index.js";
import {loadPage} from "@/api/catalog.js";
import ResultError from "@/lib/ResultError.js";
import {showErrorNotification} from "@/app/notify/index.js";
import { pushInState } from '@/common/js/helpers';
import { actionLoadItems } from '@/common/js/variables';

const store = createStore({
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
            commit('updateItems', { items: data.ITEMS });
            commit('setPagination', data.PAGINATION);
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
                commit('updateItems', { items: result.articlesList.ITEMS, append });
                commit('setPagination', result.articlesList.PAGINATION);
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
    },
});

window.addEventListener('popstate', async () => {
    try {
        showPreloader();
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        params.set('action', actionLoadItems);
        params.delete('ajax');
        url.search = params.toString();
        const result = await loadPage(url);
        store.commit('updateItems', { items: result.articlesList.ITEMS, append: false });
        store.commit('setPagination', result.articlesList.PAGINATION);
    } catch (error) {
        if(error instanceof ResultError){
            showErrorNotification(error.message);
        } else {
            showErrorNotification();
        }
        console.error('Ошибка при обработке popstate:', error);
    } finally {
        closePreloader();
    }
});


export default store;