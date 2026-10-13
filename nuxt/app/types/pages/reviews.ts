/**
 * Типы для отзывов
 */

import type { ElementDTO } from "../iblock"
import type { PageData } from "../page"
import type { FilterDTO, SectionData } from "../iblock/page"

/**
 * Данные списка отзывов с пагинацией и сортировкой
 */
export interface ReviewsDTO extends SectionData<null, FilterDTO, ElementDTO> { }

/** Тип ответа API для списка отзывов */
export type ReviewsListPageApiResponse = PageData<ReviewsDTO>