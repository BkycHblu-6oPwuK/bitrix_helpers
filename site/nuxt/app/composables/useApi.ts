import { createError } from 'h3'
import type { ApiResponse } from '~/types/api'

export function useApi<T = unknown>(
  path: string,
  options: {
    key?: string
    query?: Record<string, any>
    body?: Record<string, any> | FormData
    method?: 'get' | 'post'
  } = {}
) {
  const config = useRuntimeConfig()

  const baseURL = process.server ? config.apiBaseServer : config.public.apiBaseClient
  const cleanPath = path.replace(/^\/+/, '')

  const requestKey = options.key ?? `${cleanPath}-${JSON.stringify(options.query || {})}`

  return useAsyncData<ApiResponse<T>>(requestKey, async () => {
    try {
      const res = await $fetch<ApiResponse<T>>(cleanPath, {
        baseURL,
        method: options.method || 'get',
        query: options.query,
        body: options.body,
        headers: {
          Accept: 'application/json',
        },
      })

      if (res.status === 'error') {
        throw createError({
          statusCode: 500,
          statusMessage: res.errors?.[0]?.message || 'API returned error',
          data: res.errors,
        })
      }

      return res
    } catch (error: any) {
      throw createError({
        statusCode: error?.statusCode || 500,
        statusMessage: 'Network or server error',
      })
    }
  })
}
