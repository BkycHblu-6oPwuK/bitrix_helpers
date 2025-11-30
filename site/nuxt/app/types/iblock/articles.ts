/**
 * Типы для статей/новостей
 * Используются для списков и детальных страниц статей
 */

import type { ElementDTO } from "./element"
import type { PageData, PageDataWithElement, SectionItemsDTO } from "./content"

/**
 * Данные списка статей с пагинацией
 */
export interface ArticlesDTO {
  section: SectionItemsDTO<ElementDTO> // Секция со статьями и пагинацией
}

/** Тип ответа API для списка статей */
export type ArticlesListPageApiResponse = PageData<ArticlesDTO>

/** Тип ответа API для детальной страницы статьи */
export type ArticleElementPageApiResponse = PageDataWithElement<ElementDTO>