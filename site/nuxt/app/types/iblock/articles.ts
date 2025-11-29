import type { ElementDTO } from "./element"
import type { PaginationDTO } from "../pagination"
import type { SeoData } from "../seo"

export interface ArticlesListData {
  articles: ElementDTO[]
  pagination: PaginationDTO
  seo: SeoData
}

export interface ArticleData {
  element: ElementDTO
  seo: SeoData
}