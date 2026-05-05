// app/plugins/fetch.ts
import { $fetch } from 'ofetch'

declare module 'ofetch' {
    interface FetchOptions {
        _retry?: boolean
    }
}
/**
 * @todo вообще  вся эта тема авторизацией и получением пользователя должна быть пересмотрена
 */
export default defineNuxtPlugin((nuxtApp) => {
    const userStore = useUserStore()
    const event = useRequestEvent()
    const cookieHeader = event?.node?.req?.headers?.cookie
    let refreshPromise: Promise<any> | null = null

    nuxtApp.$fetch = $fetch.create({
        async onRequest({ options }) {
            if (options._retry) return

            if (import.meta.server && cookieHeader) {
                const headers = new Headers(options.headers as HeadersInit | undefined)
                headers.set('cookie', cookieHeader)
                options.headers = headers
            }
        },

        async onResponseError({ request, response, options }) {
            if (options._retry || response.status !== 401) return

            if (import.meta.client) {
                if (!refreshPromise) {
                    userStore.refreshTokens().then(() => true).catch(() => false).finally(() => {
                        refreshPromise = null
                    })
                }

                const success = await refreshPromise
                if (!success) return

                (nuxtApp.$fetch as typeof $fetch)(request, {
                    ...options,
                    _retry: true
                })
            }
        }
    })
})