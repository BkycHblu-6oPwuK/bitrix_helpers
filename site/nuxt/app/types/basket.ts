import type { PageData } from "./iblock/content"

/**
 * Элемент корзины
 */
export interface BasketItemDTO {
    id: number
    code: string
    offerId: number
    productId: number
    isOffer: boolean
    quantity: number
    price: number
    priceFormatted: string
    fullPrice: number
    fullPriceFormatted: string
    oldPrice: number | null
    oldPriceFormatted: string | null
    fullOldPrice: number | null
    fullOldPriceFormatted: string | null
    discountPercent: number | null
    url: string
    name: string
    previewPictureSrc: string
    detailPictureSrc: string
    properties: Record<string, any>
}

/**
 * Купон в корзине
 */
export interface CouponDTO {
    code: string
    status: 'applied' | 'none' | string
    isActive: boolean
}

/**
 * Итоговые данные корзины
 */
export interface BasketSummaryDTO {
    totalQuantity: number
    totalPrice: number
    totalPriceFormatted: string
    totalDiscount: number
    totalDiscountFormatted: string
}

/**
 * Полные данные корзины
 */
export interface BasketDataDTO {
    items: BasketItemDTO[]
    coupon: CouponDTO
    summary: BasketSummaryDTO
}

/**
 * Структура данных корзины в page
 */
export interface BasketPageDTO {
    basket: BasketDataDTO
}

/**
 * Ответ API с данными корзины
 */
export type BasketApiResponse = PageData<BasketPageDTO>

/**
 * Ответ API с идентификаторами товаров в корзине
 */
export type BasketIdsApiResponse = {
    ids: number[]
}

/**
 * Параметры для добавления товара в корзину
 */
export interface AddToBasketParams {
    offerId: number
    quantity?: number
}

/**
 * Параметры для обновления количества товара
 */
export interface UpdateBasketParams {
    offerId: number
    quantity: number
}

/**
 * Параметры для удаления товара из корзины
 */
export interface DeleteFromBasketParams {
    offerId: number
}

/**
 * Параметры для применения купона
 */
export interface ApplyCouponParams {
    coupon: string
}
