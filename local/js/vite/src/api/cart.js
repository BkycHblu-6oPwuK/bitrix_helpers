import ResultError from "@/lib/ResultError";
import { fetchHelper } from "./helper";

export const getBasket = async (detail = true) => {
    const formData = new URLSearchParams();
    formData.append('isDetail', detail ? 1 : 0);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=class&c=itb:basket&action=getCart',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const addBasket = async (productId, detail = true) => {
    const formData = new URLSearchParams();
    formData.append('productId', productId);
    formData.append('isDetail', detail ? 1 : 0);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=class&c=itb:basket&action=add',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const removeOneBasketItem = async (productId, detail = true) => {
    const formData = new URLSearchParams();
    formData.append('productId', productId);
    formData.append('isDetail', detail ? 1 : 0);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=class&c=itb:basket&action=removeOne',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const removeBasketItem = async (productId, detail = true) => {
    const formData = new URLSearchParams();
    formData.append('productId', productId);
    formData.append('isDetail', detail ? 1 : 0);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=class&c=itb:basket&action=remove',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const addCoupon = async (couponCode) => {
    const formData = new URLSearchParams();
    formData.append('couponCode', couponCode);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=class&c=itb:basket&action=addCoupon',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}