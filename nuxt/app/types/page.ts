import type { SectionDTO } from "./iblock"
import type { SeoData } from "./seo"

/**
 * Универсальный ответ API с данными страницы и SEO-метаданными.
 * @template T - Тип содержимого `page` (по умолчанию PageContent)
 */
export interface PageData<T> {
    page: T
    seo?: SeoData
}

/**
 * Универсальная структура данных для детальной страницы
 */
export interface DetailPageDTO<T = any> {
    element: T
    path: SectionDTO[] // путь к элементу, хлебные крошки
}

/**
 * Специальная форма PageData для отображения одиночного элемента (детальная страница)
 * @template T - Тип элемента (ElementDTO и т.д.)
 */
export interface PageDataWithElement<T = any> extends Omit<PageData<T>, 'page'> {
    page: DetailPageDTO<T>
}