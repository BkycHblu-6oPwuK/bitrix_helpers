import { actionLoadItems } from "@/common/js/variables";

/** filter */
export const getParamsForFilter = () => {
    const params = new URLSearchParams(window.location.search);
    params.set('action', actionLoadItems);
    if (!params.has('ajax')) {
        params.append('ajax', 'y');
    }
    return params;
}
export const getParamsForClearFilter = () => {
    const params = getParamsForFilter(paginationUrlParam);
    params.delete('sort');
    return params;
}

export const getParamsForSorting = (sortId) => {
    const params = new URLSearchParams(window.location.search);
    params.set('action', actionLoadItems);
    params.set('sort', sortId);
    return params;
}

export const deleteFilterCategoryInParams = (params, filterItems) => {
    for (const key in filterItems) {
        const { CONTROL_ID } = filterItems[key];
        params.delete(CONTROL_ID);
    }
}

export const setParams = (params, state) => {
    for (const controlId in state.selectedFilters) {
        const filterValue = state.selectedFilters[controlId];
        for (const itemKey in state.items) {
            const item = state.items[itemKey];
            for (const valueKey in item.VALUES) {
                const value = item.VALUES[valueKey];
                if (value.CONTROL_ID === controlId) {
                    params.append(value.CONTROL_ID, filterValue);
                    break;
                }
            }
        }
    }
}

export const getSelectedFilters = (items) => {
    const selectedFilters = {};
    for (const key in items) {
        const item = items[key];
        for (const valueKey in item.VALUES) {
            if (item.VALUES[valueKey].CHECKED) {
                selectedFilters[item.VALUES[valueKey].CONTROL_ID] = item.VALUES[valueKey].HTML_VALUE;
            }
        }
    }
    return selectedFilters;
}

export const getSelectedFilterHash = (selectedFilters) => {
    return JSON.stringify(JSON.parse(JSON.stringify(selectedFilters)));
}
/** end filter */