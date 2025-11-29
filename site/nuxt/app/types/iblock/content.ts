import type { CatalogItemDTO } from './catalog'
import type { FormDTO } from '../webForm/form'
import type { ElementDTO } from './element'
import type { SeoData } from '../seo'

export type PageContentType = 'main_banner' | 'slider' | 'form' | 'products' | 'slider_articles' | 'video' | 'new' | 'popular' | string

export interface PageContentItem {
  type: PageContentType
  result: Record<string, any>
}

export interface MainBannerContent {
  type: 'main_banner'
  result: { items: ElementDTO[] }
}

export interface SliderContent {
  type: 'slider'
  result: {
    title: string
    linkToAll: string
    items: CatalogItemDTO[]
  }
}

export interface FormContent {
  type: 'form'
  result: FormDTO
}

export interface SliderArticlesContent {
  type: 'slider_articles'
  result: {
    link: string
    items: ElementDTO[]
  }
}

export type PageContent = (MainBannerContent | SliderContent | FormContent | SliderArticlesContent | PageContentItem)[]

export interface PageData {
  page: PageContent
  seo: SeoData
}
