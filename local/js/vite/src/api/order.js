import ResultError from "@/lib/ResultError";
import { bxSessid } from "@/common/js/variables";
import { fetchHelper } from "./helper";

/**
 * @param {string} action 
 * @param {URLSearchParams} formData 
 */
export const send = async (action, formData) => {
    formData.append('via_ajax', 'Y');
    formData.append('soa-action', action);
    const response = await fetchHelper({
        url: '/local/components/itb/sale.order.ajax/ajax.php',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.order || result.error) {
        throw new Error;
    }
    return result.order;
}

export const getClient = async () => {
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:eshoplogistic.delivery&action=getClient',
        method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
};

/**
 * 
 * @param {*} distance расстояние в км
 * @param {*} duration время в часах
 * @returns 
 */
export const calculateDeliveryDistance = async (distance, duration) => {
    const params = new URLSearchParams({
        distance: distance,
        duration: duration,
    });

    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:eshoplogistic.delivery&action=calculateDeliveryDistance',
        formData: params, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data.price.value;
}

/**
 * @param {string} profileId 
 * @param {string} locationCode 
 * @param {Number} paymentId 
 */
export const getPvz = async (profileId, locationCode, paymentId) => {
    const params = new URLSearchParams({
        profileId: profileId,
        locationCode: locationCode,
        paymentId: paymentId,
    });

    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:eshoplogistic.delivery&action=getPvzList',
        formData: params, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
};

export const cancel = async (id) => {
    const formData = new URLSearchParams({
        id: id,
    });
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:order&action=cancel',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const initPay = async (orderId, sendSms = false) => {
    const formData = new URLSearchParams({
        orderId: orderId,
        sendSms: sendSms ? 1 : 0,
    });
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:order&action=initPay',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

/**
 * @param {string} city 
 */
export const getSdekPickupPointForCity = async (city) => {
    const formData = new URLSearchParams({
        city: city,
    });
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=itb:order&action=getSdekPickupPointForCity',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || result.data.success === false) {
        if(result.data?.error){
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}