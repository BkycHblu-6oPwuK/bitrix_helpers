import { createError } from 'h3'
import type { ApiResponse, ContentType } from '~/types/api'
import type { $Fetch } from 'ofetch'
import type { MaybeRefOrGetter, WatchSource } from 'vue'

type AppendFn = (key: string, value: string | Blob) => void
const AUTO_CONTENT_TYPE = 'form' as const
const CSRF_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE'])

function getBaseUrl() {
    const config = useRuntimeConfig()
    return config.public.apiBaseClient
}

function getCsrfHeaders(method: string): Record<string, string> {
    if (!CSRF_METHODS.has(method.toUpperCase())) return {}
    const { csrf, headerName } = useCsrf()
    return { [headerName]: csrf }
}

/**
 * Возвращает путь без ведущих и конечных слешей
 */
function getCleanPath(path: string) {
    return path.replace(/^\/+/, '').replace(/\/+$/, '')
}

function getApiFetch() {
    return (import.meta.server ? useRequestFetch() : useNuxtApp().$fetch) as $Fetch
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
    try {
        const method = options.method || 'get'
        const { body, headers } = prepareRequest(
            options.body,
            options.contentType
        )

        const fetch = getApiFetch()
        const res = await fetch<ApiResponse<T>>(getCleanPath(path), {
            baseURL: getBaseUrl(),
            method: method,
            query: options.query,
            body,
            credentials: 'include',
            headers
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
    } = {}
) {
    const baseURL = getBaseUrl()
    const cleanPath = getCleanPath(path)

    try {
        const method = options.method || 'get'
        const { body, headers } = prepareRequest(
            options.body,
            options.contentType
        )
        Object.assign(headers, getCsrfHeaders(method))

        const fetch = getApiFetch()
        const res = await fetch<ApiResponse<T>>(cleanPath, {
            baseURL,
            method: method,
            query: options.query,
            body,
            credentials: 'include',
            headers
        })

        if (res.status === 'error') {
            throw createErrorFromApi(res)
        }

        return res
    } catch (error: any) {
        if (isCsrfError(error)) {
            if(import.meta.client) {
                alert('CSRF Token Mismatch. Страница будет перезагружена.')
                location.reload()
                throw error
            }
        }
        throw createErrorFromApi(error)
    }
}

function isRawBody(body: unknown): body is BodyInit {
    return (
        typeof body === 'string' ||
        body instanceof FormData ||
        body instanceof URLSearchParams ||
        body instanceof Blob ||
        body instanceof ArrayBuffer ||
        ArrayBuffer.isView(body)
    )
}

function isPlainObject(value: unknown): value is Record<string, unknown> {
    return Object.prototype.toString.call(value) === '[object Object]'
}

function appendValue(key: string, value: unknown, append: AppendFn): void {
    if (value === undefined) return
    if (value === null) return append(key, '')
    if (value instanceof Blob) return append(key, value) // File наследует Blob
    if (value instanceof Date) return append(key, value.toISOString())

    if (Array.isArray(value)) {
        value.forEach((item) => appendValue(`${key}[]`, item, append))
        return
    }

    if (isPlainObject(value)) {
        for (const [k, v] of Object.entries(value)) {
            appendValue(`${key}[${k}]`, v, append)
        }
        return
    }

    append(key, String(value))
}

function serializeFields(data: Record<string, unknown>, append: AppendFn): void {
    for (const [key, value] of Object.entries(data)) {
        appendValue(key, value, append)
    }
}

export function prepareRequest(
    body: any,
    contentType: ContentType = 'auto'
): {
    body?: BodyInit
    headers: Record<string, string>
} {
    const headers: Record<string, string> = {
        Accept: 'application/json'
    }

    if (body === undefined || body === null) {
        return { headers }
    }

    const type = contentType === 'auto' ? AUTO_CONTENT_TYPE : contentType

    if (isRawBody(body)) {
        if (typeof body === 'string') {
            if (type === 'json') headers['Content-Type'] = 'application/json'
            if (type === 'form') headers['Content-Type'] = 'application/x-www-form-urlencoded'
        }
        return { body, headers }
    }

    switch (type) {
        case 'json': {
            headers['Content-Type'] = 'application/json'
            return { body: JSON.stringify(body), headers }
        }

        case 'form': {
            if (!isPlainObject(body)) {
                throw new TypeError('prepareRequest: для "form" нужен plain-объект')
            }
            const params = new URLSearchParams()
            serializeFields(body, (key, value) => {
                if (typeof value !== 'string') {
                    throw new TypeError('prepareRequest: файлы можно отправлять только через "multipart"')
                }
                params.append(key, value)
            })
            headers['Content-Type'] = 'application/x-www-form-urlencoded'
            return { body: params, headers }
        }

        case 'multipart': {
            if (!isPlainObject(body)) {
                throw new TypeError('prepareRequest: для "multipart" нужен plain-объект или FormData')
            }
            const formData = new FormData()
            serializeFields(body, (key, value) => formData.append(key, value))
            return { body: formData, headers }
        }

        default:
            throw new TypeError(`prepareRequest: неизвестный contentType "${contentType}"`)
    }
}
