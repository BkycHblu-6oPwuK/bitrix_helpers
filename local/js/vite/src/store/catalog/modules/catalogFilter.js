import FilterTypes from '@/lib/model/FilterTypes';
import { deleteFilterCategoryInParams, getParamsForSorting, getSelectedFilterHash, getSelectedFilters, setParams, getParamsForFilter } from '../functions';
import { loadPage } from '@/api/catalog';
import Sorting from '@/lib/model/sorting/Sorting';
import { closePreloader, showPreloader } from '@/app/preloader';
import ResultError from '@/lib/ResultError';
import { showErrorNotification } from '@/app/notify';
import { pushInState } from '@/common/js/helpers';

const catalogFilter = {
    namespaced: true,
    state: {
        filter_url: '',
        clear_url: '',
        items: {},
        types: false,
        sorting: {},
        selectedFilters: {},
        oldSelectedFiltersHash: '',
        oldSortingId: '',
    },
    mutations: {
        /**
         * @param {URL} url 
         */
        setFilterUrl(state, url) {
            state.filter_url = url;
        },
        /**
         * @param {URL} url 
         */
        setClearUrl(state, url) {
            state.clear_url = url;
        },
        setItems(state, items) {
            state.items = items;
        },
        setTypes(state, types) {
            state.types = new FilterTypes(types);
        },
        setSelectedFilters(state, value) {
            state.selectedFilters = value;
        },
        setOldSelectedFiltersHash(state, value) {
            state.oldSelectedFiltersHash = value;
        },
        pushSelectedFilters(state, { controlId, value }) {
            state.selectedFilters[controlId] = value;
        },
        removeSelectedFilters(state, controlId) {
            delete state.selectedFilters[controlId];
        },
        setSorting(state, sorting) {
            state.sorting = new Sorting(sorting);
        },
        setOldSortingId(state, value) {
            state.oldSortingId = value;
        }
    },
    actions: {
        initialize({ commit, dispatch }, data) {
            const selectedFilters = getSelectedFilters(data.items);
            dispatch('setBaseData', data);
            commit('setTypes', data.types);
            commit('setSelectedFilters', selectedFilters);
            commit('setOldSortingId', data.sorting.currentSortId);
            commit('setOldSelectedFiltersHash', getSelectedFilterHash(selectedFilters));
        },
        updateFilter({ commit, state, rootGetters }, filterItems) {
            const params = getParamsForFilter();
            params.delete(rootGetters['catalogSection/getPagination'].paginationUrlParam)
            deleteFilterCategoryInParams(params, filterItems);
            setParams(params, state);
            commit('setFilterUrl', new URL(window.location.origin + `${window.location.pathname}?${params.toString()}`));
        },
        async applyFilter({ getters, commit, dispatch }) {
            const currentFiltersHash = getSelectedFilterHash(getters.getSelectedFilters);
            if (getters.getOldSelectedFiltersHash === currentFiltersHash) return;
            commit('setOldSelectedFiltersHash', currentFiltersHash);
            await dispatch('loadAndApplyFilter', { url: getters.getFilterUrl });
            pushInState(getters.getFilterUrl);
        },
        async sorting({ commit, getters, dispatch }, sortId) {
            if (getters.getOldSortingId === sortId) return;
            commit('setOldSortingId', sortId);
            const url = new URL(window.location.href);
            url.search = getParamsForSorting(sortId).toString();
            await dispatch('loadAndApplyFilter', { url });
            pushInState(url)
        },
        async clearFilter({ getters, dispatch, rootGetters }) {
            const url = getters.getClearUrl;
            const params = getParamsForFilter();
            params.delete(rootGetters['catalogSection/getPagination'].paginationUrlParam);
            url.search = params.toString();
            await dispatch('loadAndApplyFilter', { url, isClear: true });
            pushInState(getters.getClearUrl);
        },
        async loadAndApplyFilter({ dispatch, commit }, { url, isClear = false }) {
            try {
                showPreloader();
                const result = await loadPage(url);
                dispatch('setBaseData', result.filter);
                if (isClear) {
                    commit('setOldSortingId', result.filter.sorting.currentSortId);
                    commit('setOldSelectedFiltersHash', '{}');
                    commit('setSelectedFilters', {});
                }
                dispatch('catalogSection/initialize', result.catalogSection, { root: true });
                dispatch('catalogSectionList/initialize', result.catalogSectionList, { root: true });
            } catch (error) {
                if (error instanceof ResultError) {
                    showErrorNotification(error.message);
                } else {
                    showErrorNotification();
                }
                console.error('Ошибка при загрузке фильтра:', error);
            } finally {
                closePreloader();
            }
        },
        setBaseData({ commit }, data) {
            commit('setFilterUrl', new URL(window.location.origin + data.filter_url));
            commit('setClearUrl', new URL(window.location.origin + data.clear_url));
            commit('setItems', data.items);
            commit('setSorting', data.sorting);
        }
    },
    getters: {
        getFilterUrl: (state) => state.filter_url,
        getClearUrl: (state) => state.clear_url,
        getItems: (state) => state.items,
        getTypes: (state) => state.types,
        getSelectedFilters: (state) => state.selectedFilters,
        getSorting: (state) => state.sorting,
        getOldSelectedFiltersHash: (state) => state.oldSelectedFiltersHash,
        getOldSortingId: (state) => state.oldSortingId,
        filterIsApplyed: (state) => Object.keys(state.selectedFilters).length > 0,
    }
};

export default catalogFilter;
