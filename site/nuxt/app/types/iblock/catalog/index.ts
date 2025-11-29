import type { PaginationDTO } from "~/types/pagination"

export interface CatalogFilterValueItemDTO {
  controlId: string
  htmlValue: string
  value: string
  checked: boolean
  disabled: boolean
}

export interface CatalogFilterItemDTO {
  id: number
  code: string
  name: string
  propertyType: string
  userType: string
  displayType: string
  displayExpanded: boolean
  values: CatalogFilterValueItemDTO[]
}

export interface SortingItemDTO {
  id: number
  name: string
  code: string
  sort: number
  default: boolean
  direction: string
  sortBy: string
}

export interface SortingDTO {
  currentSortId: string
  defaultSortId: string
  title: string
  availableSorting: SortingItemDTO[]
}

export interface CatalogFilterDTO {
  filterUrl: string
  clearUrl: string
  items: CatalogFilterItemDTO[]
  sorting: SortingDTO
  types: any[]
}

export interface CatalogPriceGroupDTO {
  id: string
  name: string
  base: boolean
  sort: number
  xmlId: string
}

export interface CatalogPriceDTO {
  id: string
  productId: string
  extraId: string
  catalogGroupId: string
  price: number
  currency: string
  quantityFrom: number
  quantityTo: number
  priceScale: number
  catalogGroup: CatalogPriceGroupDTO | null
}

export interface CatalogStoreProductItemDTO {
  id: number
  storeId: number
  productId: number
  amount: number
  quantityReserved: number
}

export interface CatalogProductDTO {
  id: number
  quantity: number
  quantityTrace: string
  weight: number
  timestampX?: string | null
  priceType: string
  recurSchemeLength: number
  recurSchemeType: string
  trialPriceId: number
  withoutOrder: boolean
  selectBestPrice: boolean
  vatId: number
  vatIncluded: boolean
  canBuyZero: string
  negativeAmountTrace: string
  tmpId: string
  purchasingPrice: number
  purchasingCurrency: string
  barcodeMulti: boolean
  quantityReserved: number
  subscribe: string
  width: number
  length: number
  height: number
  measure: number
  type: string
  available: boolean
  bundle: boolean
}

export interface CatalogOfferDTO {
  id: number
  active: boolean
  productId: number
  catalog: CatalogProductDTO
  prices: CatalogPriceDTO[]
  storeProduct: CatalogStoreProductItemDTO[]
  detailPageUrl: string
}

export interface CatalogItemDTO {
  id: number
  name: string
  code: string
  detailPageUrl: string
  detailText: string
  detailTextType: string
  searchableContent: string
  offers: CatalogOfferDTO[]
  preselectedOffer: CatalogOfferDTO | null
  price: CatalogPriceDTO | null
  catalog: CatalogProductDTO | null
}

export interface SectionsDTO {
  id: string
  name: string
  code: string
  url: string
  pictureSrc: string
}

export interface CatalogSectionDTO {
  items: CatalogItemDTO[]
  pagination: PaginationDTO | null
}
