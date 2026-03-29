/**
 * Типы для меню сайта
 * Поддерживают вложенную структуру пунктов
 */

/**
 * Типы меню в системе
 */
export type MenuType = 'catalog_menu' | 'bottom_menu' | 'top_menu' | string

/**
 * Пункт меню (рекурсивная структура)
 */
export interface MenuItem {
  id: number              // Уникальный ID пункта
  name: string            // Название пункта
  code: string            // Символьный код
  iblockSectionId: number // ID раздела инфоблока (0 если не привязан)
  link: string            // URL ссылка
  children: MenuItem[]    // Дочерние пункты меню
}

/**
 * Обертка для данных меню
 */
export interface MenuData {
  menu: MenuItem[] // Массив корневых пунктов меню
}
