/**
 * Типы инфоблоков Bitrix 
 * Элементы, разделы, свойства
 */

import type { FileDTO, FileSrc } from "../file"
import type { LocationDTO } from "../location"

/**
 * Базовый элемент инфоблока
 * Содержит стандартные поля Bitrix и пользовательские свойства
 */
export interface ElementDTO {
    id: number                     // Уникальный ID элемента
    code: string                   // Символьный код
    name: string                   // Название элемента

    previewText: string            // Анонс (краткое описание)
    previewPicture: string         // URL картинки анонса
    detailText: string             // Подробное описание
    detailPicture: string          // URL детальной картинки

    detailPageUrl: string          // URL детальной страницы
    listPageUrl: string            // URL списка/раздела
    dateCreate: string             // Дата создания (ISO формат)

    properties: PropertyItemDTO[]  // Массив пользовательских свойств
}

/**
 * Свойство элемента инфоблока
 * Может содержать различные типы данных: текст, число, файл, список и т.д.
 */

export interface PropertyItemDTO {
    id: number                            // Уникальный ID свойства
    code: string                           // Символьный код свойства
    name: string                           // Название свойства
    value: string | number | boolean | null | FileDTO[] | LocationDTO // Значение свойства
    type?: string | null                   // Тип свойства (string, number, file, list и т.д.)
    userType?: string | null                  // Пользовательский тип свойства (если применимо)
    xmlId?: string | null                  // Внешний код свойства
    link?: string | null                   // Ссылка на ресурс (если применимо)
    pictureSrc?: FileSrc | null             // URL картинки (если свойство - картинка)
    item?: any[] | null                     // Дополнительные данные свойства (например, для привязанных элементов, список, HL и т.д.)
}

/**
 * Тип значения свойства.
 * Может быть одиночным значением, массивом (множественное) или null
 */
export type PropertiesType = PropertyItemDTO | PropertyItemDTO[] | null

/**
 * Раздел инфоблока (категория)
 */
export interface SectionDTO {
    id: string        // ID раздела
    name: string      // Название раздела
    code: string      // Символьный код
    url: string       // URL раздела
    pictureSrc: FileSrc // URL картинки раздела
    selected?: boolean // Флаг, указывающий, выбран ли раздел
}