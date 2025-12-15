/**
 * Типы для динамических блоков контента на страницах
 * -----------------------------------------------
 * Эти типы описывают структуру данных, которые приходят с бэкенда
 * для построения страниц из отдельных блоков (баннеры, слайдеры, формы и т.д.).
 * Компоненты фронтенда принимают блоки одной из этих форм и рендерят
 * соответствующий Vue-компонент по полю `type`.
 *
 * Пример использования:
 * const page = await useApi<PageData>('get-content', { query: { pathName } })
 * page.data.value?.data?.page -> массив блоков, который перебирается и
 * рендерится динамически через <component :is="..." />
 */

import type { CatalogItemDTO } from './catalog'
import type { SeoData } from '../seo'
import type { ElementDTO, SectionDTO } from '.'
import type { FormDTO } from '../web-form'

/**
 * Доступные типы блоков контента
 * Каждый тип соответствует отдельному компоненту
 */
/**
 * Допустимые типы блоков контента, которые может вернуть бэкенд.
 * Включает конкретные типы, но допускает и произвольные строки (новые типы).
 */
export type PageContentType =
  | 'main_banner'
  | 'slider'
  | 'form'
  | 'products'
  | 'slider_articles'
  | 'video'
  | 'new'
  | 'popular'
  | 'html'
  | string

/**
 * Базовый (универсальный) интерфейс блока контента.
 * Используется как fallback для неизвестных/новых типов блоков.
 * Поле `result` хранит необработанные данные — рендерёр может
 * передать их в `Dump` или в заглушку для отладки.
 */
export interface ContentItemDTO {
  type: PageContentType // Тип блока
  result: Record<string, any> // Неявно типизированные данные блока
}

/**
 * Блок главного баннера — обычно содержит массив элементов (картинки/слайды)
 */
export interface MainBannerContent {
  type: 'main_banner'
  result: MainBannerDTO
}

/**
 * Блок слайдера (карусель товаров или произвольных карточек)
 */
export interface SliderContent {
  type: 'slider'
  result: ProductSliderDTO
}

/**
 * Блок с web-формой (структура определяется типами форм в `../web-form.ts`)
 */
export interface FormContent {
  type: 'form'
  result: FormDTO
}

/**
 * Блок: слайдер со статьями (содержит ссылку и массив элементов)
 */
export interface SliderArticlesContent {
  type: 'slider_articles'
  result: ListArticlesDTO
}

/**
 * Блок с произвольным HTML-контентом.
 * Используйте осторожно — данные должны быть безопасны для вставки.
 */
export interface HtmlContent {
  type: 'html'
  result: {
    content: string // HTML строка для вставки
  }
}

/**
 * Блок с видео
 */
/**
 * Блок видео: может содержать список элементов с видео или ссылками
 */
export interface VideoContent {
  type: 'video'
  result: VideoDTO
}

/**
 * DTO для главного баннера: массив элементов (ElementDTO содержит базовые поля)
 */
export interface MainBannerDTO {
  items: ElementDTO[]
}

/**
 * DTO для блока видео — использует такую же структуру элементов
 */
export interface VideoDTO {
  items: ElementDTO[]
}

/**
 * DTO для списка статей в слайдере
 */
export interface ListArticlesDTO {
  link: string
  items: ElementDTO[]
}

/**
 * DTO для слайдера с продуктами: заголовок, ссылка "все" и массив товаров
 */
export interface ProductSliderDTO {
  title: string
  linkToAll: string
  items: CatalogItemDTO[]
}

/**
 * PageContent — массив блоков страницы.
 * Каждый элемент может быть одного из известных интерфейсов или универсальным объектом.
 */
export type PageContent = (
  | MainBannerContent
  | SliderContent
  | FormContent
  | SliderArticlesContent
  | HtmlContent
  | VideoContent
  | ContentItemDTO
  | Record<string, any>
)[]

/**
 * Универсальный ответ API с данными страницы и SEO-метаданными.
 * @template T - Тип содержимого `page` (по умолчанию PageContent)
 */
export interface PageData<T = PageContent> {
  page: T
  seo: SeoData
}

/**
 * Универсальная структура данных для детальной страницы
 */
export interface DetailPageDTO<T = any> {
  element: T
  path: Record<string, SectionDTO> // путь к элементу, хлебные крошки
}

/**
 * Специальная форма PageData для отображения одиночного элемента (детальная страница)
 * @template T - Тип элемента (ElementDTO и т.д.)
 */
export interface PageDataWithElement<T = any> extends Omit<PageData, 'page'> {
  page: DetailPageDTO<T>
}