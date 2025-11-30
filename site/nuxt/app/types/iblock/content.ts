import type { CatalogItemDTO } from './catalog'
import type { FormDTO } from '../webForm/form'
import type { ElementDTO } from './element'
import type { SeoData } from '../seo'

export type PageContentType = 'main_banner' | 'slider' | 'form' | 'products' | 'slider_articles' | 'video' | 'new' | 'popular'  | 'html' | string

export interface ContentItemDTO {
  type: PageContentType
  result: Record<string, any>
}

export interface MainBannerContent {
  type: 'main_banner'
  result: MainBannerDTO
}

export interface SliderContent {
  type: 'slider'
  result: ProductSliderDTO
}

export interface FormContent {
  type: 'form'
  result: FormDTO
}

export interface SliderArticlesContent {
  type: 'slider_articles'
  result: ListArticlesDTO
}

export interface HtmlContent {
  type: 'html'
  result: {
    content: string
  }
}

export interface VideoContent {
  type: 'video'
  result: VideoDTO
}

export interface MainBannerDTO {
  items: ElementDTO[]
}

export interface VideoDTO {
  items: ElementDTO[]
}

export interface ListArticlesDTO {
  link: string
  items: ElementDTO[]
}

export interface ProductSliderDTO {
  title: string
  linkToAll: string
  items: CatalogItemDTO[]
}

export type PageContent = (MainBannerContent | SliderContent | FormContent | SliderArticlesContent | HtmlContent | VideoContent | ContentItemDTO | Record<string, any>)[]

export interface PageData<T = PageContent> {
  page: T
  seo: SeoData
}
