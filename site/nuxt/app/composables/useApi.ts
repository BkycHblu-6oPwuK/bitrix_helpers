import { createError } from 'h3'
import type { ApiResponse } from '~/types/api'

type ContentType = 'json' | 'form' | 'multipart' | 'auto'

function getBaseUrl() {
  const config = useRuntimeConfig()
  return process.server
    ? config.apiBaseServer
    : config.public.apiBaseClient
}

function getCleanPath(path: string) {
  return path.replace(/^\/+/, '')
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
  path: string,
  options: {
    key?: string
    query?: Record<string, any>
    body?: any
    method?: 'get' | 'post'
    lazy?: boolean
    contentType?: ContentType
  } = {}
) {
  const baseURL = getBaseUrl()
  const cleanPath = getCleanPath(path)
  const requestKey =
    options.key ?? `${cleanPath}-${JSON.stringify(options.query || {})}`

  const asyncDataFn = options.lazy ? useLazyAsyncData : useAsyncData

  return asyncDataFn<ApiResponse<T>>(requestKey, async () => {
    try {
      const { body, headers } = prepareRequest(
        options.body,
        options.contentType
      )

      const { $fetch } = useNuxtApp()

      const res = await $fetch<ApiResponse<T>>(cleanPath, {
        baseURL,
        method: options.method || 'get',
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
  })
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
    method?: 'get' | 'post'
    contentType?: ContentType
  } = {}
) {
  const baseURL = getBaseUrl()
  const cleanPath = getCleanPath(path)

  try {
    const { body, headers } = prepareRequest(
      options.body,
      options.contentType
    )

    const { $fetch } = useNuxtApp()

    const res = await $fetch<ApiResponse<T>>(cleanPath, {
      baseURL,
      method: options.method || 'get',
      query: options.query,
      body,
      credentials: 'include',
      headers
    })

    if (res.status === 'error') {
      throw new Error(res.errors?.[0]?.message || 'API error')
    }

    return res
  } catch (error: any) {
    throw new Error(error?.message || 'Network error')
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

  if (!body) {
    return { headers }
  }

  if (
    contentType === 'multipart' ||
    (contentType === 'auto' && body instanceof FormData)
  ) {
    return { body, headers }
  }

  if (contentType === 'json') {
    headers['Content-Type'] = 'application/json'
    return {
      body: JSON.stringify(body),
      headers
    }
  }

  if (contentType === 'form' || contentType === 'auto') {
    headers['Content-Type'] = 'application/x-www-form-urlencoded'

    return {
      body: new URLSearchParams(
        Object.entries(body).map(([k, v]) => [k, String(v)])
      ),
      headers
    }
  }

  return { headers }
}
