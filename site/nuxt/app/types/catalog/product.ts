export interface ProductPrice {
  priceValue: number
  priceFormatted: string
  oldPriceValue: number
  oldPriceFormatted: string
  discountPercent: number
  priceTypeId: number
}

export interface ProductOffer {
  id: number
  productId: number
  price: ProductPrice
  available: boolean
  active: boolean
}

export interface ProductItem {
  id: number
  name: string
  code: string
  price: ProductPrice
  offers: ProductOffer[]
  preselectedOffer: ProductOffer
}