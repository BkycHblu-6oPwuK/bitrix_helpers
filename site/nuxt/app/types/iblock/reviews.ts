/**
 * Типы для отзывов
 */

import type { ElementDTO } from "."
import type { PageData } from "./content"
import type { FilterDTO, SectionData } from "./page"

/**
 * Данные списка отзывов с пагинацией и сортировкой
 */
export interface ReviewsDTO extends SectionData<null, FilterDTO, ElementDTO> {}

/** Тип ответа API для списка отзывов */
export type ReviewsListPageApiResponse = PageData<ReviewsDTO>