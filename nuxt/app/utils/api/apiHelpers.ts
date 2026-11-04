import type { H3Event } from 'h3'

type AppendFn = (key: string, value: string | Blob) => void
const AUTO_CONTENT_TYPE = 'form' as const

const CONDITIONAL_HEADERS = [
    'if-none-match',
    'if-modified-since',
    'if-match',
    'if-unmodified-since',
    'if-range',
]

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

/** На SSR useRequestFetch проксирует валидаторы кеша страницы — для внутренних API-запросов они не нужны */
export function dropConditionalHeaders({ options }: { options: any }) {
    if (!import.meta.server) return
    const headers = new Headers(options.headers)
    for (const name of CONDITIONAL_HEADERS) headers.delete(name)
    options.headers = headers
}

/**
 * Страница получает самую свежую Last-Modified из всех API-запросов, сделанных при SSR
 */
export function mergeApiCacheHeaders(
    event: H3Event | undefined,
    headers: Headers,
) {
    if (!event) return

    const etag = headers.get('etag')

    if (etag) {
        const etags: string[] = event.context.__apiEtags ??= []
        etags.push(etag)
    }

    const raw = headers.get('last-modified')
    if (!raw) return

    const next = Date.parse(raw)
    if (Number.isNaN(next)) return

    const current: number = event.context.__lastModified ?? 0

    if (next > current) {
        event.context.__lastModified = next
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