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

    properties: PropertiesMap      // Свойства элемента, ключ - символьный код свойства
}

/**
 * Запись справочника (HL-блок, свойство типа directory).
 * Поля UF_* зависят от конкретного HL-блока, поэтому остальные ключи допускаются.
 */
export interface HlItem {
    ID: string
    UF_XML_ID: string
    UF_NAME?: string
    UF_FILE?: string
    UF_LINK?: string
    UF_DESCRIPTION?: string
    UF_FULL_DESCRIPTION?: string
    UF_SORT?: string
    UF_EXTERNAL_CODE?: string
    PICTURE_SRC?: string | null
    [field: string]: unknown
}


/**
 * Значение свойства типа "список" (enum).
 */
export interface EnumItem {
    ID: number
    PROPERTY_ID?: number | null
    VALUE: string
    DEF?: boolean | null
    SORT: number
    XML_ID: string
    TMP_ID?: string | null
}

/**
 * Привязанный элемент инфоблока (свойство типа "привязка к элементам").
 * Набор полей зависит от выборки, гарантирован только ID.
 */
export interface LinkedElementItem {
    ID: number
    NAME?: string
    CODE?: string
    DETAIL_PAGE_URL?: string
    [field: string]: unknown
}

/**
 * Данные, прикреплённые к значению свойства
 */
export type PropertyValueItem = HlItem | EnumItem | LinkedElementItem

/**
 * Одно значение свойства.
 * Общие данные свойства (code, name, type) лежат в PropertyItemDTO.
 */
export interface PropertyValueDTO<TItem extends PropertyValueItem = PropertyValueItem> {
    value: string | number | boolean | null | FileDTO | LocationDTO // Значение (для файла - один FileDTO на значение)
    cleanValue: unknown                    // Исходное значение без обработки (~VALUE)
    description: string                    // Описание значения
    item: TItem | null                     // Доп. данные: запись HL, элемент списка, привязанный элемент
}

/**
 * Свойство элемента инфоблока.
 * Метаданные хранятся один раз, значения - списком (и для одиночных, и для множественных свойств).
 */
export interface PropertyItemDTO<TItem extends PropertyValueItem = PropertyValueItem> {
    id: number                             // ID свойства
    code: string                           // Символьный код свойства
    name: string                           // Название свойства
    type: string | null                    // Тип свойства: S, N, L, F, E, G
    userType: string | null                // Пользовательский тип (directory, HTML, map_google и т.д.)
    valuesDto: PropertyValueDTO<TItem>[]      // Значения свойства
}

/**
 * Свойства элемента: код свойства -> свойство
 */
export type PropertiesMap = Record<string, PropertyItemDTO>

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