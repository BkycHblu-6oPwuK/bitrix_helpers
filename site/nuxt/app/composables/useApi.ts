import { createError } from 'h3'
import type { ApiResponse } from '~/types/api'

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
 * @returns Результат useAsyncData с данными и ошибками
 */
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

  // Используем разные URL для сервера (внутренний) и клиента (внешний)
  const baseURL = process.server ? config.apiBaseServer : config.public.apiBaseClient
  const cleanPath = path.replace(/^\/+/, '') // Убираем начальные слеши

  // Генерируем уникальный ключ для кеширования на основе пути и параметров
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

      // Проверяем статус ответа от API (формат ApiResponse)
      if (res.status === 'error') {
        throw createError({
          statusCode: 500,
          statusMessage: res.errors?.[0]?.message || 'API returned error',
          data: res.errors,
        })
      }

      return res
    } catch (error: any) {
      // Обрабатываем сетевые ошибки и ошибки сервера
      throw createError({
        statusCode: error?.statusCode || 500,
        statusMessage: 'Network or server error',
      })
    }
  })
}
