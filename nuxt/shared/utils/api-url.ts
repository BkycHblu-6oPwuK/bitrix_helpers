/** Путь без ведущих и конечных слешей */
export function getCleanPath(path: string) {
    return path.replace(/^\/+/, '').replace(/\/+$/, '')
}

/**
 * Склейка базового пути API и относительного пути.
 * joinApiUrl('/api/v1/', '/sitemap') → '/api/v1/sitemap'
 * joinApiUrl('https://x.com/api/v1', 'altcha/get-challenge') → 'https://x.com/api/v1/altcha/get-challenge'
 */
export function joinApiUrl(base: string | undefined, path: string) {
    const cleanBase = (base ?? '').replace(/\/+$/, '')
    return `${cleanBase}/${getCleanPath(path)}`
}

export function getBaseUrl() {
    const config = useRuntimeConfig()
    return config.public.apiBaseClient
}

/**
 * Получение полного относительного URL API по относительному пути.
 */
export function getApiUrl(path: string) {
    return joinApiUrl(getBaseUrl(), path)
}