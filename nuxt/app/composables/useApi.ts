import { createError } from 'h3'
import type { ApiResponse, ContentType } from '~/types/api'
import type { $Fetch } from 'ofetch'
import type { MaybeRefOrGetter, WatchSource } from 'vue'

function getApiFetch() {
    return useRequestFetch() as unknown as $Fetch
}

/**
 * Универсальный композабл для API запросов с кешированием
 * Использует useAsyncData для SSR и автоматического кеширования
 * Поддерживает разные baseURL для серверной и клиентской стороны
 * 
 * @template T - Тип ожидаемых данных в ответе
 * @param path - Путь к API endpoint (без базового URL)
 * @param options - Настройки запроса
 *   - key: уникальный ключ для кеширования (генерируется автоматически если не указан)
 *   - query: объект с query параметрами
 *   - body: тело запроса (для POST)
 *   - method: HTTP метод (get или post)
 *   - lazy: использовать useLazyAsyncData (true) или useAsyncData (false, по умолчанию)
 * @returns Результат useAsyncData с данными и ошибками
 */
export function useApi<T = unknown>(
    path: MaybeRefOrGetter<string>,
    options: {
        key?: string
        query?: Record<string, any>
        body?: any
        method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
        lazy?: boolean
        contentType?: ContentType
        watch?: WatchSource[]
    } = {}
) {
    const requestKey = options.key ?? `${getCleanPath(toValue(path))}-${JSON.stringify(options.query || {})}`
    const asyncDataFn = options.lazy ? useLazyAsyncData : useAsyncData

    return asyncDataFn<ApiResponse<T>>(requestKey, () => fetchApi<T>(toValue(path), options), { watch: options.watch })
}

/**
 * Один запрос к API для загрузки страницы (внутри useAsyncData и подобных).
 * В отличие от useApiFetch, ошибка — createError с кодом ответа: 404 API даёт 404-страницу Nuxt
 * без scrf токена
 */
export async function fetchApi<T = unknown>(
    path: string,
    options: {
        query?: Record<string, any>
        body?: any
        method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
        contentType?: ContentType
    } = {}
) {
    const method = options.method || 'get'
    const event = import.meta.server ? useRequestEvent() : undefined
    const fetch = getApiFetch()
    const { onRequest, onResponseError } = createAuthFetchHooks()
    try {
        const { body, headers } = prepareRequest(options.body, options.contentType)

        const res = await fetch<ApiResponse<T>>(getCleanPath(path), {
            baseURL: getBaseUrl(),
            method,
            query: options.query,
            body,
            credentials: 'include',
            headers,
            onRequest: (ctx) => {
                dropConditionalHeaders(ctx)
                onRequest(ctx)
            },
            onResponseError,
            onResponse({ response }) {
                mergeApiCacheHeaders(event, response.headers)
            },
        })

        if (res.status === 'error') {
            throw createError({
                statusCode: 500,
                statusMessage: res.errors?.[0]?.message || 'API error',
                data: res.errors
            })
        }

        return res
    } catch (error: any) {
        throw createError({
            statusCode: error?.statusCode || 500,
            statusMessage: error?.message || 'Network error'
        })
    }
}

/**
 * Композабл для прямых API запросов без кеширования
 * Используется для запросов после монтирования компонента или в обработчиках событий
 * 
 * @template T - Тип ожидаемых данных в ответе
 * @param path - Путь к API endpoint (без базового URL)
 * @param options - Настройки запроса
 * @returns Promise с результатом запроса
 */
export async function useApiFetch<T = unknown>(
    path: string,
    options: {
        query?: Record<string, any>
        body?: any
        method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
        contentType?: ContentType
        altcha?: boolean
    } = {}
) {
    const method = options.method || 'get'
    const baseURL = getBaseUrl()
    const cleanPath = getCleanPath(path)
    const event = import.meta.server ? useRequestEvent() : undefined
    const fetch = getApiFetch()
    const { onRequest, onResponseError } = createAuthFetchHooks() // до await getAltchaHeader()
    try {
        const { body, headers } = prepareRequest(
            options.body,
            options.contentType
        )
        Object.assign(headers, getCsrfHeaders(method))

        if (options.altcha && import.meta.client) {
            const altchaHeader = await getAltchaHeader()
            if (Object.keys(altchaHeader).length === 0) {
                throw new Error('Не удалось пройти проверку Altcha, попробуйте ещё раз')
            }
            Object.assign(headers, altchaHeader)
        }

        const res = await fetch<ApiResponse<T>>(cleanPath, {
            baseURL,
            method,
            query: options.query,
            body,
            credentials: 'include',
            headers,
            onRequest: (ctx) => {
                dropConditionalHeaders(ctx)
                onRequest(ctx)
            },
            onResponseError,
            onResponse({ response }) {
                mergeApiCacheHeaders(event, response.headers)
            },
        })
        if (res.status === 'error') {
            throw createErrorFromApi(res)
        }

        return res
    } catch (error: any) {
        if (isCsrfError(error)) {
            if (import.meta.client) {
                alert('CSRF Token Mismatch. Страница будет перезагружена.')
                location.reload()
                throw error
            }
        }
        throw createErrorFromApi(error)
    }
}