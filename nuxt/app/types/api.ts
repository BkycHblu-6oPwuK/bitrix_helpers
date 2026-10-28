/**
 * Типы для стандартного API ответа от сервера
 * Используются во всех API запросах
 */

/**
 * Структура ошибки от API
 */
export type ErrorItem = {
    code: number,            // Код ошибки
    customData: null | Record<string, unknown>, // Дополнительные данные ошибки
    message: string          // Текстовое описание ошибки
}

export type AppError = Error & {
    errors?: ErrorItem[]
    statusCode?: number
    statusMessage?: string
}

/**
 * Успешный ответ API
 * @template T - Тип данных в ответе
 */
export type ApiSuccess<T> = {
    status: 'success'; // Статус успеха
    data: T;           // Полезная нагрузка
    errors: []         // Пустой массив ошибок
}

/**
 * Ответ API с ошибкой
 */
export type ApiError = {
    status: 'error';   // Статус ошибки
    data: null;        // Данных нет
    errors: ErrorItem[]    // Массив ошибок
}

/**
 * Структура преобразованных ошибок, возвращаемых API и различными исключениями
 */
export type ApiErrors = {
    _general?: string
    [key: string]: string | undefined
}

/**
 * Типы контента для запросов
 */
export type ContentType = 'json' | 'form' | 'multipart' | 'auto'

/**
 * Объединенный тип API ответа (успех или ошибка)
 * @template T - Тип данных при успешном ответе
 */
export type ApiResponse<T> = ApiSuccess<T> | ApiError