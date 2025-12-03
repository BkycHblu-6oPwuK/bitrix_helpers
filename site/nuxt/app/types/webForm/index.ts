/**
 * Типы для web-форм Bitrix
 * Используются для отображения и обработки форм на сайте
 */

/**
 * Поле web-формы
 * Описывает одно поле ввода (текст, email, select и т.д.)
 */
export interface FormNewFieldDTO {
  id: number                        // Уникальный ID поля
  name: string                      // Имя поля (name атрибут)
  label: string                     // Лейбл поля (видимое название)
  type: string                      // Тип поля (text, email, select, textarea и т.д.)
  required: boolean                 // Обязательное поле
  isMultiple: boolean               // Множественное значение (для select, checkbox)
  attributes: Record<string, any>   // HTML атрибуты (placeholder, maxlength и т.д.)
  options: Record<string, any>      // Варианты для select/radio (key: value)
  error: string                     // Текст ошибки валидации
}

/**
 * Полные данные web-формы
 * Содержит метаданные формы и все поля
 */
export interface FormDTO {
  id: number                           // ID формы в Bitrix
  title: string                        // Заголовок формы
  description: string                  // Описание формы
  fields: FormNewFieldDTO[]            // Массив полей формы
  formIdsMap: Record<string, string>   // Маппинг ID полей для обработки
  error: string                        // Общая ошибка формы
  successAdded: boolean                // Флаг успешной отправки
}

/**
 * Запрос на сохранение формы
 * Используется при отправке формы на сервер
 */
export interface FormStoreRequest {
  form: FormDTO // Данные формы для отправки
}