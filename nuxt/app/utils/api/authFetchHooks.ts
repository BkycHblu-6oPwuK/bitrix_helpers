import { appendResponseHeader, getRequestHeader } from 'h3'
import type { H3Event } from 'h3'
import type { $Fetch, FetchContext, FetchResponse } from 'ofetch'

declare module 'ofetch' {
    interface FetchOptions {
        _retry?: boolean
    }
}

type ResponseErrorContext = FetchContext & { response: FetchResponse<any> }
type RequestFetch = (url: string, options?: Record<string, any>) => Promise<unknown>

interface ServerAuthState {
    /** Актуальный cookie-заголовок после refresh (undefined, пока refresh не выполнялся) */
    cookieHeader?: string
    refreshPromise: Promise<boolean> | null
}

export interface AuthFetchHooks {
    onRequest: (ctx: FetchContext) => void
    onResponseError: (ctx: ResponseErrorContext) => Promise<void>
}

/** На клиенте один refresh на все параллельные 401 */
let clientRefreshPromise: Promise<boolean> | null = null

/** Состояние живёт в event.context, то есть оно своё для каждого SSR-запроса */
function getServerState(event: H3Event): ServerAuthState {
    return (event.context.__apiAuth ??= { refreshPromise: null })
}

/**
 * Накатывает новые set-cookie на текущий cookie-заголовок.
 * Merge по имени: иначе при дубликатах (старый + новый access_token)
 * большинство парсеров возьмут первое, то есть старое значение.
 */
function mergeCookieHeader(current: string | undefined, setCookies: string[]): string {
    const jar = new Map<string, string>()

    const put = (pair: string, dropEmpty: boolean) => {
        const i = pair.indexOf('=')
        if (i <= 0) return
        const name = pair.slice(0, i).trim()
        const value = pair.slice(i + 1).trim()
        if (dropEmpty && !value) jar.delete(name)
        else jar.set(name, value)
    }

    for (const part of (current ?? '').split(';')) put(part, false)
    for (const c of setCookies) put(c.split(';')[0] ?? '', true)

    return [...jar].map(([k, v]) => `${k}=${v}`).join('; ')
}

async function refreshOnServer(
    event: H3Event,
    requestFetch: RequestFetch,
    state: ServerAuthState,
    refreshUrl: string,
    csrfHeaders: Record<string, string>,
): Promise<boolean> {
    try {
        const baseCookie = state.cookieHeader ?? getRequestHeader(event, 'cookie')
        let setCookies: string[] = []
        await requestFetch(refreshUrl, {
            method: 'POST',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
                ...csrfHeaders,
                ...(baseCookie ? { cookie: baseCookie } : {}),
            },
            onResponse({ response }: { response: Response }) {
                setCookies = response.headers.getSetCookie?.() ?? []
            },
        })

        if (setCookies.length) {
            appendResponseHeader(event, 'set-cookie', setCookies)
            state.cookieHeader = mergeCookieHeader(baseCookie, setCookies)
        }

        return true
    } catch (error) {
        console.error(error)
        return false
    }
}

/**
 * Хуки для $fetch:
 * - SSR: после refresh подставляет обновлённые cookie в следующие запросы;
 * - при 401 тихо обновляет токены (один refresh на все параллельные запросы)
 *   и повторяет запрос.
 *
 * ВАЖНО: вызывать синхронно в начале композабла/функции (до любого await),
 * пока доступен контекст Nuxt.
 */
export function createAuthFetchHooks(): AuthFetchHooks {
    const event = import.meta.server ? useRequestEvent() : undefined
    const requestFetch = import.meta.server ? (useRequestFetch() as unknown as RequestFetch) : undefined
    const userStore = import.meta.client ? useUserStore() : undefined
    let refreshUrl = getApiUrl('user/refresh')
    let csrfHeaders: Record<string, string> = {}

    if (import.meta.server) {
        csrfHeaders = getCsrfHeaders('post')
    }

    function refresh(): Promise<boolean> {
        if (event && requestFetch) {
            const state = getServerState(event)
            return (state.refreshPromise ??= refreshOnServer(
                event, requestFetch, state, refreshUrl, csrfHeaders,
            ).finally(() => {
                state.refreshPromise = null
            }))
        }

        if (userStore) {
            return (clientRefreshPromise ??= userStore
                .refreshTokens()
                .then(() => true, () => false)
                .finally(() => {
                    clientRefreshPromise = null
                }))
        }

        return Promise.resolve(false)
    }

    return {
        onRequest({ options }) {
            // Базовые cookie useRequestFetch пробрасывает сам.
            // Подменяем только если refresh уже выдал новые.
            const cookie = event ? getServerState(event).cookieHeader : undefined
            if (!cookie) return

            const headers = new Headers(options.headers as HeadersInit | undefined)
            headers.set('cookie', cookie)
            options.headers = headers
        },

        async onResponseError({ request, response, options }) {
            if (options._retry || response.status !== 401) return

            const url = typeof request === 'string' ? request : (request as Request).url
            if (url.includes('/user/refresh')) return

            if (!(await refresh())) return

            // Повтор делает сам ofetch (retry): он заново прогонит onRequest
            // с обновлёнными cookie. _retry защищает от бесконечного цикла.
            options._retry = true
            options.retry = 1
            options.retryDelay = 0
            options.retryStatusCodes = [401]
        },
    }
}