import type { ProductItem } from './catalog/product'
import type { SeoData } from './seo'

export type PageContentType = 'main_banner' | 'slider' | 'text_block' | 'products' | string

export interface PageContentItem {
  type: PageContentType
  result: any
}

export interface MainBannerItem {
  ID: string
  NAME: string
  PREVIEW_PICTURE?: {
    SRC: string
    ALT: string
    TITLE: string
  }
  PROPERTIES?: {
    LINK?: {
      VALUE: string
    }
    DESCRIPTION?: {
      VALUE?: string
    }
  }
}

export interface MainBannerContent {
  type: 'main_banner'
  result: MainBannerItem[]
}

export interface SliderContent {
  type: 'slider'
  result: {
    title: string
    linkToAll: string | null
    items: Record<string, ProductItem>
  }
}

export type PageContent = (MainBannerContent | SliderContent | PageContentItem)[]

export interface PageData {
  page: PageContent
  seo: SeoData
}
