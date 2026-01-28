/**
 * Типы для статей/новостей
 * Используются для списков и детальных страниц статей
 */

import type { ElementDTO } from "./iblock"
import type { ArticlesDTO } from "./iblock/articles"
import type { PageData, PageDataWithElement } from "./page"

/** Тип ответа API для списка статей */
export type ArticlesListPageApiResponse = PageData<ArticlesDTO>

/** Тип ответа API для детальной страницы статьи */
export type ArticleElementPageApiResponse = PageDataWithElement<ElementDTO>