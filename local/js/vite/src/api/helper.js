import { bxSessid } from "@/common/js/variables";

/**
 * Функция для отправки запросов на сервер
 * @param {string} url - адрес запроса
 * @param {URLSearchParams|Object|null} formData - данные для отправки
 * @param {string} method - метод запроса (GET, POST и т. д.)
 * @param {Object} headers - заголовки запроса
 */
export const fetchHelper = async ({
    url,
    formData = null,
    method = 'GET',
    headers = {}
}) => {
    headers = {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest',
        ...headers
    };

    if(!formData && method !== 'GET') {
        formData = new URLSearchParams();
    } else if (formData && !(formData instanceof URLSearchParams)) {
        formData = new URLSearchParams(formData);
    }

    if(formData) {
        formData.append('sessid', bxSessid);
    }

    const options = {
        method,
        headers,
        ...(method !== 'GET' && { body: formData })
    };

    const response = await fetch(url, options);

    if (!response.ok) {
        throw new Error(`Ошибка HTTP: ${response.status}`);
    }

    return response;
}