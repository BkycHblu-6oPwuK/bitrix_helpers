// app/plugins/fetch.ts
import { $fetch } from 'ofetch'

export default defineNuxtPlugin((nuxtApp) => {
  const userStore = useUserStore()
  const event = useRequestEvent()
  const cookieHeader = event?.node?.req?.headers?.cookie
  let refreshPromise: Promise<any> | null = null

  nuxtApp.$fetch = $fetch.create({
    async onRequest({ options }) {
      if (options._retry) return

      if (process.server && cookieHeader) {
        options.headers = {
          ...(options.headers as Record<string, string>),
          cookie: cookieHeader
        }
      }
    },

    async onResponseError({ request, response, options }) {
      if (options._retry || response.status !== 401) return
      
      if (process.client) {
        if (!refreshPromise) {
          userStore.refreshTokens().then(() => true).catch(() => false).finally(() => {
            refreshPromise = null
          })
        }

        const success = await refreshPromise
        if (!success) return

        return nuxtApp.$fetch(request, {
          ...options,
          _retry: true
        })
      }
    }
  })
})