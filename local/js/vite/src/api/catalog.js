import ResultError from "@/lib/ResultError";
import { fetchHelper } from "./helper";

/**
 * @param {URL} url 
 * @param {callback} callback 
 * @return JSON
 */
export const loadPage = async (url) => {
    const response = await fetchHelper({
        url: url.toString(),
        method: 'GET'
    });
    const result = await response.json();
    return result;
}

export const search = async (query) => {
    const formData = new URLSearchParams();
    formData.append('query', query);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:search&action=search',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    return result;
}

export const toggleFavourite = async (productId) => {
    const formData = new URLSearchParams();
    formData.append('productID', productId);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?action=itb:favorite.FavoriteController.toggle',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    return result;
}

export const getFavouriteIds = async () => {
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?action=itb:favorite.FavoriteController.get',
        method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const toggleDressing = async (offerId, detail = true) => {
    const formData = new URLSearchParams();
    formData.append('offerId', offerId);
    formData.append('isDetail', detail ? 1 : 0);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?action=itb:dressing.DressingController.toggle',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const getDressing = async (detail = true) => {
    const formData = new URLSearchParams();
    //formData.append('sessid', bxSessid);
    formData.append('isDetail', detail ? 1 : 0);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?action=itb:dressing.DressingController.get',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const createDressingOrder = async (formData) => {
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?action=itb:dressing.DressingController.createOrder',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const changeColor = async ({ url, signedParameters, id }) => {
    const formData = new URLSearchParams();
    formData.append('signedParameters', signedParameters);
    formData.append('selectedProduct', id);
    const response = await fetchHelper({
        url: url,
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data) {
        throw new Error;
    }
    return result.data;
}

export const addReview = async (url, productId, form) => {
    const formData = new URLSearchParams();
    formData.append('product_id', productId);
    formData.append('form', JSON.stringify(form));
    const response = await fetchHelper({
        url: url,
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data) {
        throw new Error;
    }
    return result.data;
}

export const addProductVieweded = async (productId, siteId, parentId = 0) => {
    const formData = new URLSearchParams();
    formData.append('AJAX', 'Y');
    formData.append('PRODUCT_ID', productId);
    formData.append('SITE_ID', siteId);
    formData.append('PARENT_ID', parentId);
    const response = await fetchHelper({
        url: '/bitrix/components/bitrix/catalog.element/ajax.php',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result) {
        throw new Error;
    }
    return result;
}