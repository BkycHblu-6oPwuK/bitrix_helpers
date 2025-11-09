import type { ProductItem } from './catalog/product'
import type { FormDTO } from './webForm/form'
import type { ElementDTO } from './iblock/element'
import type { SeoData } from './seo'

export type PageContentType = 'main_banner' | 'slider' | 'form' | 'products' | 'slider_articles' | 'video' | string

export interface PageContentItem {
  type: PageContentType
  result: any
}

export interface MainBannerContent {
  type: 'main_banner'
  result: ElementDTO[]
}

export interface SliderContent {
  type: 'slider'
  result: {
    title: string
    link: string | null
    items: Record<string, ProductItem>
  }
}

export interface FormContent {
  type: 'form'
  result: FormDTO
}

export interface SliderArticlesContent {
  type: 'slider_articles'
  result: {
    title: string
    items: ElementDTO[]
  }
}

export type PageContent = (MainBannerContent | SliderContent | FormContent | SliderArticlesContent | PageContentItem)[]

export interface PageData {
  page: PageContent
  seo: SeoData
}
