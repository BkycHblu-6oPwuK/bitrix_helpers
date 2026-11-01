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
    const baseURL = getBaseUrl()

    let cookieHeader = event?.node?.req?.headers?.cookie
    let refreshPromise: Promise<boolean> | null = null

    function refreshOnClient(): Promise<boolean> {
        return userStore
            .refreshTokens()
            .then(() => true)
            .catch(() => false)
    }

    async function refreshOnServer(): Promise<boolean> {
        try {
            const requestFetch = useRequestFetch() as unknown as $Fetch

            const res = await requestFetch.raw('user/refresh', {
                baseURL,
                method: 'POST',
                credentials: 'include',
                headers: cookieHeader ? { cookie: cookieHeader } : undefined,
                _retry: true,
            })

            const setCookies = res.headers.getSetCookie?.() ?? []
            if (setCookies.length && event?.node?.res) {
                const existing = event.node.res.getHeader('set-cookie')
                const merged = ([] as string[]).concat(
                    (existing as string[] | string | undefined) ?? [],
                    setCookies,
                )
                event.node.res.setHeader('set-cookie', merged)

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

            if (options._retry || response.status !== 401) return

            const url = typeof request === 'string' ? request : request.url
            if (url.includes('/user/refresh')) return

            if (!refreshPromise) {
                const run = import.meta.server ? refreshOnServer : refreshOnClient
                refreshPromise = run().finally(() => {
                    refreshPromise = null
                })
            }

            const success = await refreshPromise
            if (!success) return

            const retryOptions = { ...options, _retry: true }
            if (import.meta.server && cookieHeader) {
                const headers = new Headers(retryOptions.headers as HeadersInit | undefined)
                headers.set('cookie', cookieHeader)
                retryOptions.headers = headers
            }

            ctx.response = await ($fetch as typeof $fetch).raw(request, retryOptions)
        },
    })
})