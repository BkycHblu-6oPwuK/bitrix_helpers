import ResultError from "@/lib/ResultError";
import { fetchHelper } from "./helper";

export const register = async ({ name, email, phone, password }) => {
    const formData = new URLSearchParams();
    formData.append('firstName', name);
    formData.append('email', email);
    formData.append('phoneNumber', phone);
    formData.append('password', password);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:auth&action=register',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    return result.data;
}

export const authorize = async ({ email, password }) => {
    const formData = new URLSearchParams();
    formData.append('email', email);
    formData.append('password', password);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:auth&action=authorize',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    return result.data;
}

export const sendCode = async (phone) => {
    const formData = new URLSearchParams();
    formData.append('phoneNumber', phone);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:auth&action=sendCode',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}

export const checkCode = async (phone, code) => {
    const formData = new URLSearchParams();
    formData.append('phoneNumber', phone);
    formData.append('code', code);
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:auth&action=checkCode',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result.data;
}