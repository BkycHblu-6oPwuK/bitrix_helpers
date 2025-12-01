/**
 * Типы для секции страниц (фильтрация, сортировка и элементы с пагинацией и разделами)
 * например - каталолог, статьи, новости и т.д.
 */

import type { PaginationDTO } from "../pagination"

/**
 * Универсальная структура данных для секции с фильтрацией и пагинацией
 * @template S - Тип элементов в списке (товары, статьи и т.д.)
 * @template F - Тип данных фильтра
 * @template SL - Тип списка разделов (массив или null)
 */
export interface SectionData<SL = any[]|null, F = FilterDTO|null, S = any> {
    sectionList: SL // Список дочерних разделов (для навигации)
    filter: F // Данные фильтра (поля, сортировка, выбранные значения)
    section: SectionItemsDTO<S> // Элементы с пагинацией
}

/**
 * Структура секции с массивом элементов и информацией о пагинации.
 * Используется компонентами секции/каталога.
 */
export interface SectionItemsDTO<T = any> {
    items: T[]
    pagination: PaginationDTO | null
}

/**
 * Настройки сортировки для каталога
 */
export interface SortingDTO {
    currentSortId: string         // ID текущей выбранной сортировки
    defaultSortId: string         // ID сортировки по умолчанию
    title: string                 // Заголовок блока сортировки
    availableSorting: SortingItemDTO[] // Массив доступных вариантов
    requestParam: string          // Название параметра в URL (обычно 'sort')
}

/**
 * Один вариант сортировки
 */
export interface SortingItemDTO {
    id: number        // ID варианта сортировки
    name: string      // Название ("По цене", "По популярности")
    code: string      // Символьный код
    sort: number      // Порядок отображения
    default: boolean  // Является ли дефолтной
    direction: string // Направление (asc/desc)
    sortBy: string    // Поле для сортировки
}


/**
 * Полная структура фильтра каталога
 * Содержит все поля фильтрации, сортировку и служебные URL
 */
export interface FilterDTO {
    filterUrl: string        // URL для применения фильтров
    clearUrl: string         // URL для сброса всех фильтров
    items: FilterItemDTO[]   // Массив всех доступных фильтров
    sorting: SortingDTO      // Настройки сортировки
    types: {                 // Константы типов отображения фильтров
        checkbox: string       // Тип "чекбокс"
        radio: string          // Тип "радио"
        dropdown: string       // Тип "выпадающий список"
        range: string          // Тип "диапазон" (от-до)
        numbers: string        // Тип "числовой"
        calendar: string       // Тип "календарь"
    }
}

/**
 * Одно значение фильтра (checkbox, radio option)
 */
export interface FilterValueItemDTO {
    controlId: string   // ID контрола в форме (для привязки к URL параметру)
    htmlValue: string   // Значение для отправки на сервер
    value: string       // Отображаемое значение (может содержать HTML)
    checked: boolean    // Выбрано ли значение
    disabled: boolean   // Заблокировано ли значение (нет товаров)
}

/**
 * Один пункт фильтра (группа значений)
 * Например: "Производитель", "Цвет", "Размер"
 */
export interface FilterItemDTO {
    id: number                  // Уникальный ID фильтра
    code: string                // Символьный код свойства
    name: string                // Название фильтра
    propertyType: string        // Тип свойства в Bitrix
    userType: string            // Пользовательский тип
    displayType: string         // Тип отображения (checkbox, radio, range)
    displayExpanded: boolean    // Развернут ли фильтр по умолчанию
    values: FilterValueItemDTO[] // Массив доступных значений
}

/**
 * Раздел каталога (категория)
 */
export interface SectionDTO {
    id: string        // ID раздела
    name: string      // Название раздела
    code: string      // Символьный код
    url: string       // URL раздела
    pictureSrc: string // URL картинки раздела
}

/**
 * Список разделов каталога
 */
export interface SectionsDTO {
    sectionList: SectionDTO[] // Массив разделов
}
