import type { Pagination } from "../pagination"
import type { SeoData } from "../seo"

export interface CatalogFilterValue {
  CONTROL_ID: string
  CONTROL_NAME: string
  CONTROL_NAME_ALT: string
  HTML_VALUE_ALT: number
  HTML_VALUE: string
  VALUE: string
  SORT: number
  UPPER: string
  FLAG: string | null
  URL_ID: string
  CHECKED: boolean
  DISABLED: boolean
}

export interface CatalogFilterItem {
  ID: string
  IBLOCK_ID: string
  CODE: string
  NAME: string
  PROPERTY_TYPE: string
  DISPLAY_TYPE: string
  DISPLAY_EXPANDED: 'Y' | 'N'
  FILTER_HINT: string
  VALUES: Record<string, CatalogFilterValue>
  ENCODED_ID: string
  DECIMALS: number
}

export interface CatalogFilterSorting {
  currentSortId: string
  defaultSortId: string
  title: string
  availableSorting: {
    fieldId: string
    id: string
    name: string
    sortBy: string
    order: string
  }[]
}

export interface CatalogFilter {
  filter_url: string
  clear_url: string
  items: Record<string, CatalogFilterItem>
  sorting: CatalogFilterSorting
  types: Record<string, string>
}

export interface CatalogSectionItem {
  id: number
  parent_section_id: number
  name: string
  code: string
  url: string
  picture: string
}

export interface CatalogSectionList {
  sections: CatalogSectionItem[]
}

export interface CatalogSection {
  items: any[]
  pagination: Pagination
}

export interface CatalogPage {
  catalogFilter: CatalogFilter
  catalogSectionList: CatalogSectionList
  catalogSection: CatalogSection
}

export interface CatalogData {
  page: CatalogPage
  seo: SeoData
}
