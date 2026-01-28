/**
 * Типы для web-форм Bitrix
 * Используются для отображения и обработки форм на сайте
 */

import type { FileSrc } from "./file"
import type { PageData } from "./page"

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
  imageSrc: FileSrc                     // URL изображения формы
  description: string                  // Описание формы
  dateFormat: string               // Формат даты для полей типа date
  fields: FormNewFieldDTO[]            // Массив полей формы
  formIdsMap: Record<string, string>   // Маппинг ID полей для обработки
  error: string                        // Общая ошибка формы
  successAdded: boolean                // Флаг успешной отправки
}

/**
 * Ответ при отправке web-формы
 */
export type FormStoreResponse = PageData<{
  form: FormDTO
}>