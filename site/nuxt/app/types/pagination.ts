/**
 * Типы для пагинации списков
 * Используются в каталоге, статьях и других списках
 */

/**
 * Информация об одной странице в пагинации
 */
export interface PaginationPage {
  pageNumber: number   // Номер страницы
  isSelected: boolean  // Является ли текущей
}

/**
 * Полные данные пагинации с сервера
 */
export interface PaginationDTO {
  pages: PaginationPage[]     // Массив всех страниц
  pageSize: number            // Количество элементов на страницу
  currentPage: number         // Номер текущей страницы
  pageCount: number           // Общее количество страниц
  paginationUrlParam: string  // Название URL параметра для пагинации (напр.: 'page')
}