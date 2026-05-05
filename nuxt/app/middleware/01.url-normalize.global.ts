// Этот мидлвар выполняется на всех страницах и на сервере, и на клиенте. Он нормализует URL, чтобы избежать дублей и проблем с SEO.
// удалить если реализация не нужна, или перенесена в nginx config

import { defineNuxtRouteMiddleware, navigateTo, useRequestHeaders, useRequestURL } from '#imports'

function normalizePath(path: string): string {
    let normalized = (path || '/').toLowerCase().replace(/\/+/g, '/');

    if (!normalized.startsWith('/')) {
        normalized = `/${normalized}`;
    }

    if (!normalized.endsWith('/') && normalized.length > 1) {
        normalized += '/';
    }

    return normalized;
}

export default defineNuxtRouteMiddleware((to) => {
    if (import.meta.prerender) {
        return
    }

    const requestUrl = import.meta.server ? useRequestURL() : null

    // to.path отражает целевой путь роутера — корректен и на клиенте, и на сервере.
    // requestUrl.pathname — это оригинальный HTTP-запрос, он не меняется при внутренних
    // редиректах Nuxt, что приводит к бесконечному циклу нормализации на сервере.
    const targetPath = to.path || '/'
    const targetSearch = import.meta.server
        ? (requestUrl?.search || '')
        : (to.fullPath.includes('?') ? `?${to.fullPath.split('?')[1]?.split('#')[0]}` : '')
    const targetHash = import.meta.server
        ? ''
        : to.hash || ''

    // --- 1. WWW → non-WWW (SSR only)
    if (import.meta.server) {
        const headers = useRequestHeaders(['host'])
        const host = headers.host

        if (host?.startsWith('www.')) {
            const newHost = host.replace(/^www\./, '')
            return navigateTo(`https://${newHost}${to.fullPath}`, {
                redirectCode: 301
            })
        }
    }

    // --- 2. Исключения
    if (
        targetPath === '/' ||
        targetPath.trim() === '' ||
        targetPath.startsWith('/_nuxt') ||
        targetPath.startsWith('/assets') ||
        targetPath.startsWith('/local_assets')
    ) {
        return
    }

    // --- 3. Нормализация пути назначения
    const normalizedPath = normalizePath(targetPath)

    // --- 4. Если ничего не изменилось — выходим
    if (normalizedPath === targetPath) {
        return
    }

    // --- 5. Защита от бесконечного редиректа:
    //        если уже пришли сюда редиректом с пути, который нормализуется
    //        в то же самое — значит мы в цикле, останавливаемся
    if (to.redirectedFrom && normalizePath(to.redirectedFrom.path) === normalizedPath) {
        return
    }

    const finalPath = `${normalizedPath}${targetSearch}${targetHash}`

    return navigateTo(finalPath, { redirectCode: import.meta.server ? 302 : 301 })
})