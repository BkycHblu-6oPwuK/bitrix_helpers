import ResultError from "@/lib/ResultError";
import { fetchHelper } from "./helper";

export const getPersonal = async () => {
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=getPersonal',
        method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const getOrders = async (page = 1, filter = {}) => {
    const formData = new URLSearchParams({
        page: page,
        filter: JSON.stringify(filter)
    })
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=getOrders',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const getDressing = async (page = 1) => {
    const formData = new URLSearchParams({
        page: page
    })
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=getDressing',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const getQuestions = async () => {
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=getQuestions',
        method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const updateField = async (field, value) => {
    const formData = new URLSearchParams({
        field: field,
        value: value
    })
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=updateField',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const addPreferenceNotification = async (type, channel, enabled) => {
    const formData = new URLSearchParams({
        type: type,
        channel: channel,
        enabled: enabled ? 1 : 0
    })
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:notification&action=addPreference',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

/*
export const updateEmail = async (email, password) => {
    const formData = new URLSearchParams({
        email: email,
        password: password
    })
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=updateEmail',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const updatePassword = async (password, oldPassword) => {
    const formData = new URLSearchParams({
        password: password,
        oldPassword: oldPassword,
    })
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=updatePassword',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}

export const updateProfile = async (name, phone) => {
    const formData = new URLSearchParams({
        name: name,
        phone: phone,
    })
    const response = await fetchHelper({
        url: '/bitrix/services/main/ajax.php?mode=ajax&c=beeralex:profile&action=updateProfile',
        formData: formData, method: 'POST'
    });
    const result = await response.json();
    if (!result.data || !result.data.success) {
        if (result.data?.error) {
            throw new ResultError(result.data.error);
        }
        throw new Error;
    }
    return result;
}*/