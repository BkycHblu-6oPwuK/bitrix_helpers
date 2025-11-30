/**
 * Типы для стандартного API ответа от сервера
 * Используются во всех API запросах
 */

/**
 * Структура ошибки от API
 */
export type Error = {
    code: Number,            // Код ошибки
    customData: null|Object, // Дополнительные данные ошибки
    message: string          // Текстовое описание ошибки
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
    errors: Error[]    // Массив ошибок
}

/**
 * Объединенный тип API ответа (успех или ошибка)
 * @template T - Тип данных при успешном ответе
 */
export type ApiResponse<T> = ApiSuccess<T> | ApiError