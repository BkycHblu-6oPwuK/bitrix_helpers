/**
 * Типы для каталога товаров и фильтрации
 * Включает структуры для товаров, торговых предложений, цен, фильтров и сортировки
 */

import type { PageData, SectionItemsDTO } from './content'

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
 * Ценовая группа (тип цены в Bitrix)
 * Например: "Базовая", "Оптовая", "Розничная"
 */
export interface CatalogPriceGroupDTO {
  id: string      // ID группы цен
  name: string    // Название группы
  base: boolean   // Базовая ли группа
  sort: number    // Порядок сортировки
  xmlId: string   // Внешний код
}

/**
 * Цена товара/предложения
 * Может быть несколько цен разных типов для одного товара
 */
export interface CatalogPriceDTO {
  id: string                            // ID цены
  productId: string                     // ID товара
  extraId: string                       // ID наценки
  catalogGroupId: string                // ID группы цен
  price: number                         // Сумма цены
  currency: string                      // Валюта (RUB, USD и т.д.)
  quantityFrom: number                  // Минимальное количество для цены
  quantityTo: number                    // Максимальное количество
  priceScale: number                    // Масштаб цены
  catalogGroup: CatalogPriceGroupDTO | null // Связанная группа цен
}

/**
 * Остатки товара на складе
 */
export interface CatalogStoreProductItemDTO {
  id: number                // ID записи остатка
  storeId: number           // ID склада
  productId: number         // ID товара
  amount: number            // Количество на складе
  quantityReserved: number  // Зарезервированное количество
}

/**
 * Торговая информация о товаре (из модуля "Торговый каталог")
 * Содержит данные о количестве, ценах, размерах и других характеристиках
 */
export interface CatalogProductDTO {
  id: number                      // ID товара в каталоге
  quantity: number                // Доступное количество
  quantityTrace: string           // Режим количественного учета (Y/N/D)
  weight: number                  // Вес товара
  timestampX?: string | null      // Время последнего изменения
  priceType: string               // Тип цены
  recurSchemeLength: number       // Длина схемы регулярных платежей
  recurSchemeType: string         // Тип схемы регулярных платежей
  trialPriceId: number            // ID пробной цены
  withoutOrder: boolean           // Можно ли купить без заказа
  selectBestPrice: boolean        // Выбирать ли лучшую цену
  vatId: number                   // ID ставки НДС
  vatIncluded: boolean            // Включен ли НДС в цену
  canBuyZero: string              // Можно ли купить при нулевом остатке
  negativeAmountTrace: string     // Разрешить отрицательный остаток
  tmpId: string                   // Временный ID
  purchasingPrice: number         // Закупочная цена
  purchasingCurrency: string      // Валюта закупки
  barcodeMulti: boolean           // Множественные штрихкоды
  quantityReserved: number        // Зарезервированное количество
  subscribe: string               // Разрешена ли подписка
  width: number                   // Ширина (см)
  length: number                  // Длина (см)
  height: number                  // Высота (см)
  measure: number                 // Единица измерения
  type: string                    // Тип товара (1-простой, 3-с торг.предл., 4-набор)
  available: boolean              // Доступен ли товар для покупки
  bundle: boolean                 // Является ли набором
}

/**
 * Торговое предложение (SKU) товара
 * Например: размер, цвет, конфигурация товара
 */
export interface CatalogOfferDTO {
  id: number                               // ID предложения
  active: boolean                          // Активно ли предложение
  productId: number                        // ID родительского товара
  catalog: CatalogProductDTO               // Торговая информация предложения
  prices: CatalogPriceDTO[]                // Цены предложения
  storeProduct: CatalogStoreProductItemDTO[] // Остатки на складах
  detailPageUrl: string                    // URL детальной страницы
}

/**
 * Товар в каталоге (основной тип)
 * Может содержать торговые предложения (offers) или быть простым товаром
 */
export interface CatalogItemDTO {
  id: number                           // ID элемента инфоблока
  name: string                         // Название товара
  code: string                         // Символьный код
  detailPageUrl: string                // URL детальной страницы
  detailText: string                   // Описание товара
  detailTextType: string               // Тип текста (html/text)
  searchableContent: string            // Индексируемый контент для поиска
  offers: CatalogOfferDTO[]            // Массив торговых предложений (для товаров с SKU)
  preselectedOffer: CatalogOfferDTO | null // Предвыбранное предложение
  prices: CatalogPriceDTO[]            // Цены товара (для простых товаров)
  catalog: CatalogProductDTO | null    // Торговая информация (для простых товаров)
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

/**
 * Полные данные страницы каталога
 * Включает список разделов, фильтр и список товаров с пагинацией
 */
export interface CatalogDTO {
  sectionList: SectionDTO[]              // Дочерние разделы (подкатегории)
  filter: FilterDTO                      // Фильтр с настройками
  section: SectionItemsDTO<CatalogItemDTO> // Товары с пагинацией
}

/** Тип ответа API для страницы списка разделов */
export type SectionsPageApiResponse = PageData<SectionsDTO>

/** Тип ответа API для страницы каталога с товарами */
export type CatalogPageApiResponse = PageData<CatalogDTO>

