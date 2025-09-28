import { bxSessid } from "./variables"

export function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

/**
 * 
 * @param {Number} n 
 * @param {String[]} forms 
 * @returns 
 */
export const plural = (n, forms) => {
    if (n % 10 === 1 && n % 100 !== 11) return forms[0];
    if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) return forms[1];
    return forms[2];
}

export const startCountdown = (countdownBtn, seconds = 60) => {
    countdownBtn.disabled = true;

    const countdown = setInterval(function () {
        countdownBtn.textContent = `Отправить код повторно - 00:${seconds.toString().padStart(2, '0')}`;
        seconds--;
        if (seconds < 0) {
            clearInterval(countdown);
            countdownBtn.textContent = "Отправить код повторно";
            countdownBtn.disabled = false;
        }
        countdownBtn.disabled
            ? countdownBtn.style.backgroundColor = '#9CA3AF'
            : countdownBtn.style.backgroundColor = '#111827'
    }, 1000);
}

export const logOut = () => {
    const url = new URL(window.location.origin);
    url.searchParams.set('logout', 'yes');
    url.searchParams.set('sessid', bxSessid);
    window.location.href = url.toString();
};

/**
 * @param {URL} url 
 */
export const pushInState = (url) => {
    const params = new URLSearchParams(url.search);
    params.delete('ajax');
    params.delete('action');
    url.search = params.toString();
    let urlString = url.toString();
    window.history.pushState({ url: urlString }, '', urlString);
}

export const wait = async (condition, maxRetries = 20) => {
    let retry = 0;
    while (!condition() && retry < maxRetries) {
        await new Promise(resolve => setTimeout(resolve, 100));
        retry++;
    }
}

export const getImagePublicPath = (path) => {
    return `/${import.meta.env.VITE_BASE_PATH}/public${path}`;
}