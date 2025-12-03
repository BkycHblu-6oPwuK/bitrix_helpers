/**
 * Типы для статей/новостей
 * Используются для списков и детальных страниц статей
 */

import type { ElementDTO } from "./element"
import type { PageData, PageDataWithElement } from "./content"
import type { SectionData } from "./page"

/**
 * Данные списка статей с пагинацией
 */
export interface ArticlesDTO extends SectionData<null, null, ElementDTO> {}

/** Тип ответа API для списка статей */
export type ArticlesListPageApiResponse = PageData<ArticlesDTO>

/** Тип ответа API для детальной страницы статьи */
export type ArticleElementPageApiResponse = PageDataWithElement<ElementDTO>