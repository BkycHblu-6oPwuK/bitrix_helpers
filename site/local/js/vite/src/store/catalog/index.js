import { createStore } from 'vuex';
import catalogFilter from './modules/catalogFilter';
import catalogSection from './modules/catalogSection';
import catalogSectionList from "@/store/catalog/modules/catalogSectionList.js";
import { getSelectedFilterHash, getSelectedFilters } from './functions';
import ResultError from '@/lib/ResultError';
import { showErrorNotification } from '@/app/notify';
import { closePreloader, showPreloader } from '@/app/preloader';
import { loadPage } from '@/api/catalog';
import { actionLoadItems } from '@/common/js/variables';

const store = createStore({
    modules: {
        catalogFilter: catalogFilter,
        catalogSectionList: catalogSectionList,
        catalogSection: catalogSection,
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
        store.dispatch('catalogSection/initialize', result.catalogSection);
        const filter = result.filter;
        if(filter){
            const selectedFilters = getSelectedFilters(filter.items);
            store.dispatch('catalogFilter/setBaseData', filter);
            store.commit('catalogFilter/setOldSortingId', filter.sorting.currentSortId);
            store.commit('catalogFilter/setOldSelectedFiltersHash', getSelectedFilterHash(selectedFilters));
            store.commit('catalogFilter/setSelectedFilters', selectedFilters);
        }
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
