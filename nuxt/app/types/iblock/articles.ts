/**
 * Типы для статей/новостей
 * Используются для списков и детальных страниц статей
 */

import type { ElementDTO } from "."
import type { SectionData } from "./page"

/**
 * Данные списка статей с пагинацией
 */
export interface ArticlesDTO extends SectionData<null, null, ElementDTO> {}