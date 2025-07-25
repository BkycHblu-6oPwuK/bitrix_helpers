import { body, bxSessid, headerModals } from "./variables"

//открытие модалки при наведении
export const isHoveredWithOverlay = (menuSelector, modalOverlaySelector, mouseenterCallback = null, mouseleaveCallback = null) => {
    menuSelector.addEventListener('mouseenter', () => {
        checkModal(headerModals, modalOverlaySelector)
        openModal(modalOverlaySelector)
        if (mouseenterCallback) {
            mouseenterCallback();
        }
    })
    menuSelector.addEventListener('mouseleave', () => {
        closeModal(modalOverlaySelector)
        if (mouseleaveCallback) {
            mouseleaveCallback();
        }
    })
    closeByEscAndOverlay(modalOverlaySelector, 'header-modal')
}

// открытие и закрытие модальных окон
export const openModal = (selector) => {
    selector.classList.add('visible')
    body.classList.add('body-scroll-lock')
}
export const closeModal = (selector, bodyLockRemove = true) => {
    selector.classList.remove('visible')
    if (bodyLockRemove) {
        body.classList.remove('body-scroll-lock')
    }
}
export const toggleModalVisibility = (selector, e) => {
    selector.classList.contains('visible')
        ? closeModal(selector)
        : openModal(selector)
}
export const checkModal = (modalsArray, currentModal) => {
    modalsArray.forEach(modal => {
        if (modal.classList.contains('visible') && modal !== currentModal) {
            closeModal(modal)
        }
    })
}
export const closeByEscAndOverlay = (modalOverlaySelector, closeSelector) => {
    modalOverlaySelector.addEventListener('mousedown', (e) => {
        e.target.classList.contains(closeSelector) && closeModal(modalOverlaySelector)
    })
    document.addEventListener('keydown', (e) => {
        e.key === 'Escape' && closeModal(modalOverlaySelector)
    })
}

//открытие формы (регистрация, авторизация и т.д.)
export const openForm = (popupArray, popup, hideClass) => {
    popupArray.forEach(popup => {
        popup.classList.add(hideClass)
        body.classList.remove('body-form-lock')
    })
    popup.classList.remove(hideClass)
    body.classList.add('body-form-lock')
}

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