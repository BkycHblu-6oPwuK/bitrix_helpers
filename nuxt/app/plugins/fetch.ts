// app/plugins/fetch.ts
import { $fetch } from 'ofetch'
import type { $Fetch } from 'ofetch'

declare module 'ofetch' {
    interface FetchOptions {
        _retry?: boolean
    }
}

/**
 * Плагин переопределяет глобальный $fetch, добавляя:
 * - проброс входящих cookie в upstream при SSR;
 * - "тихий" refresh access-токена при 401 с последующим ретраем запроса;
 * - дедупликацию refresh (один /user/refresh на все параллельные 401);
 * - поддержку refresh как на клиенте, так и при SSR
 *   (новые set-cookie от refresh пробрасываются в браузер и в ретрай).
 *
 * @todo вообще вся эта тема с авторизацией и получением пользователя должна быть пересмотрена
 */
export default defineNuxtPlugin((nuxtApp) => {
    const userStore = useUserStore()
    const event = useRequestEvent()

    // Актуальная строка cookie для upstream-запросов на SSR.
    // Может обновляться после refresh, чтобы ретрай ушёл уже с новым токеном.
    let cookieHeader = event?.node?.req?.headers?.cookie

    // Общий на весь плагин промис refresh — чтобы параллельные 401
    // запускали /user/refresh только один раз (дедупликация).
    let refreshPromise: Promise<boolean> | null = null

    /**
     * Клиентский refresh: cookie обновляет браузер сам (httpOnly set-cookie
     * приходит в ответе refresh-запроса), нам достаточно факта успеха.
     */
    function refreshOnClient(): Promise<boolean> {
        return userStore
            .refreshTokens()
            .then(() => true)
            .catch(() => false)
    }

    /**
     * SSR refresh: делаем низкоуровневый вызов через useRequestFetch (Nitro-прокси),
     * чтобы достать set-cookie от бэкенда, пробросить их в исходящий ответ браузеру
     * и подмешать в cookieHeader — тогда ретрай в том же SSR-проходе уйдёт с новым токеном.
     *
     * Важно: apiBaseClient (/api/v1) — относительный путь, поэтому здесь нужен именно
     * useRequestFetch(), который умеет резолвить относительные URL к самому Nitro-серверу.
     */
    async function refreshOnServer(): Promise<boolean> {
        try {
            const config = useRuntimeConfig()
            const baseURL = config.public.apiBaseClient
            const requestFetch = useRequestFetch() as unknown as $Fetch

            const res = await requestFetch.raw('user/refresh', {
                baseURL,
                method: 'POST',
                credentials: 'include',
                headers: cookieHeader ? { cookie: cookieHeader } : undefined,
                _retry: true, // не даём этому запросу самому попасть в refresh-логику
            })

            const setCookies = res.headers.getSetCookie?.() ?? []
            if (setCookies.length && event?.node?.res) {
                // Пробрасываем новые cookie в браузер
                const existing = event.node.res.getHeader('set-cookie')
                const merged = ([] as string[]).concat(
                    (existing as string[] | string | undefined) ?? [],
                    setCookies,
                )
                event.node.res.setHeader('set-cookie', merged)

                // Обновляем cookieHeader, чтобы последующий ретрай ушёл с новым токеном
                const pairs = setCookies.map((c: string) => c.split(';')[0]).filter(Boolean)
                cookieHeader = [cookieHeader, ...pairs].filter(Boolean).join('; ')
            }

            return true
        } catch {
            return false
        }
    }

    nuxtApp.$fetch = $fetch.create({
        async onRequest({ options }) {
            if (options._retry) return

            if (import.meta.server && cookieHeader) {
                const headers = new Headers(options.headers as HeadersInit | undefined)
                headers.set('cookie', cookieHeader)
                options.headers = headers
            }
        },

        async onResponseError(ctx) {
            const { request, response, options } = ctx

            // Не рефрешим повторно и реагируем только на 401
            if (options._retry || response.status !== 401) return

            // Не пытаемся рефрешить сам эндпоинт refresh — иначе бесконечный цикл
            const url = typeof request === 'string' ? request : request.url
            if (url.includes('/user/refresh')) return

            // Один общий refresh на все параллельные 401
            if (!refreshPromise) {
                const run = import.meta.server ? refreshOnServer : refreshOnClient
                refreshPromise = run().finally(() => {
                    refreshPromise = null
                })
            }

            const success = await refreshPromise
            if (!success) return

            // На SSR подменяем cookie в ретрае актуальным значением (после refresh)
            const retryOptions = { ...options, _retry: true }
            if (import.meta.server && cookieHeader) {
                const headers = new Headers(retryOptions.headers as HeadersInit | undefined)
                headers.set('cookie', cookieHeader)
                retryOptions.headers = headers
            }

            // Повторяем исходный запрос и подменяем ответ, чтобы вызывающий код
            // получил успешный результат, а не 401
            ctx.response = await ($fetch as typeof $fetch).raw(request, retryOptions)
        }
    })
})