import { fetchHelper } from "./helper";

/**
 * 
 * @param {string} query 
 * @returns 
 */
export const getLocation = async (query, pageSize = 20, page = 0) => {
    const formData = new URLSearchParams({
        query: query,
        pageSize: pageSize,
        page: page,
    });
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:location&action=get',
        formData: formData,
        method: 'POST'
    });
    const result = await response.json();
    if (!result || !result.data.data) {
        throw new Error;
    }
    return new BitrixLocation(result.data.data);
}