import { createError } from 'h3'
import type { UseFetchOptions } from '#app'
import type { ApiResponse } from '~/types/api'

export function useApi<T = unknown>(
  path: string,
  options: UseFetchOptions<ApiResponse<T>> = {}
) {
  const config = useRuntimeConfig()
  const baseURL = process.server ? config.apiBaseServer : config.public.apiBaseClient
  const cleanPath = path.replace(/^\/+/, '')

  let body = options.body
  if (body && !(body instanceof FormData) && !(body instanceof URLSearchParams)) {
    if (typeof body === 'object') {
      body = new URLSearchParams(
        Object.entries(body).reduce((acc, [key, value]) => {
          // Приведение значений к строке
          acc[key] = value != null ? String(value) : ''
          return acc
        }, {} as Record<string, string>)
      )
    }
  }

  return useFetch<ApiResponse<T>>(cleanPath, {
    baseURL,
    headers: {
      Accept: 'application/json',
      ...(options.headers || {}),
    },
    ...options,
    body, // 👈 подменили тело запроса
    async onResponse({ response }) {
      const body = response._data as ApiResponse<T>
      if (body?.status === 'error') {
        console.error(`⚠️ API error: ${baseURL}/${cleanPath}`, body.errors)
        throw createError({
          statusCode: 500,
          statusMessage: body.errors?.[0]?.message || 'API returned error',
          data: body.errors,
        })
      }
      if (options?.onResponse) await options.onResponse({ response })
    },
    async onRequestError({ error }) {
      console.error(`❌ Network/API error: ${baseURL}/${cleanPath}`, error)
      throw createError({
        statusCode: (error as any)?.statusCode || 500,
        statusMessage: 'Network or server error',
      })
    },
  })
}
