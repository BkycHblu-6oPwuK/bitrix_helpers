/**
 * Типы для свойств элементов инфоблоков Bitrix
 * Используются для хранения дополнительных полей элементов
 */

/**
 * Свойство элемента инфоблока
 * Может содержать различные типы данных: текст, число, файл, список и т.д.
 */

export interface PropertyItemDTO {
  id: number                            // Уникальный ID свойства
  code: string                           // Символьный код свойства
  name: string                           // Название свойства
  value: string | number | boolean | null // Значение свойства
  type?: string | null                   // Тип свойства (string, number, file, list и т.д.)
  xmlId?: string | null                  // Внешний код свойства
  link?: string | null                   // Ссылка на ресурс (если применимо)
  pictureSrc?: string | null             // URL картинки (если свойство - картинка)
}

/**
 * Тип значения свойства.
 * Может быть одиночным значением, массивом (множественное) или null
 */
export type PropertiesType = PropertyItemDTO | PropertyItemDTO[] | null