/**
 * Типы оформления заказа
 * Зеркалят DTO бэкенда: beeralex.api/lib/Domain/Checkout/DTO
 */

import type { PageData } from "../page"

export interface CheckoutItemDTO {
    id: number
    code: string
    offerId: number
    productId: number
    name: string
    quantity: number
    price: number
    priceFormatted: string
    oldPrice: number
    oldPriceFormatted: string
    fullPrice: number
    fullPriceFormatted: string
    fullOldPrice: number
    fullOldPriceFormatted: string
    discountPercent: number
    canBuy: boolean
    url: string
    pictureSrc: string
    article: string
}

export interface DeliveryDTO {
    id: number
    code: string
    name: string
    ownName: string
    description: string
    currency: string
    sort: number
    extraServices: ExtraServiceDTO[]
    isStoreDelivery: boolean
    isCourier: boolean
    isPickup: boolean
    logotip: string
    isSelected: boolean
    storeList: StoreDTO[]
    price: number
    priceFormatted: string
    deliveryPeriod: string
}

export interface ExtraServiceDTO {
    id: string
    title: string
    price: number
    priceFormatted: string
    value: string | number | null
    values: ExtraServiceValueDTO[]
}

export interface ExtraServiceValueDTO {
    id: number
    title: string
    price: number
    priceFormatted: string
}

export interface StoreDTO {
    id: number
    title: string
    address: string
    phone: string
    schedule: string
    coordinates: number[]
}

export interface PaymentDTO {
    id: number
    name: string
    description: string
    logotip: string
}

export interface PersonTypeItemDTO {
    name: {
        default: string
        mobile: string
    }
    checked: boolean
}

export interface PropertyDTO {
    id: number
    code: string
    name: string
    type: string
    value: any
    required: boolean
    pattern: string
    isZip: boolean
    isLocation: boolean
    isPhone: boolean
    isEmail: boolean
    isAddress: boolean
    minLength: number
    maxLength: number
    multiple: boolean
}

export interface CheckoutDeliveryDTO {
    message: string
    location: string
    city: string
    address: string
    postCode: string
    deliveries: Record<string, DeliveryDTO>
    minDeliveryPrice: number
    minDeliveryPriceFormatted: string
    selectedId: number
    storeSelectedId: number
    selectedPvz: string
}

export interface CheckoutTotalDTO {
    basket: number
    basketFormatted: string
    basketWithoutDiscount: number
    basketWithoutDiscountFormatted: string
    delivery: number
    deliveryFormatted: string
    discount: number
    discountFormatted: string
    total: number
    totalFormatted: string
    quantity: number
    positionQuantity: number
}

export interface CheckoutPersonTypeDTO {
    selected: string
    oldPersonType: string
    types: Record<string, PersonTypeItemDTO>
}

export interface CheckoutCouponDTO {
    value: string
    isVerified: boolean
    discount: number
    discountFormatted: string
}

export interface PropIdsMap {
    personType: string
    personTypeOld: string
    paySystem: string
    delivery: string
    profileId: string
    profileChange: string
    extraServices: string
    locationType: string
    locationModeSteps: string
    comment: string
    payments: Record<string, number>
    personTypes: Record<string, number>
    properties: Record<string, string>
    shopId: string
    postCodeChanged: string
}

export interface CheckoutDTO {
    items: CheckoutItemDTO[]
    total: CheckoutTotalDTO
    activePay: string
    payments: Record<string, PaymentDTO>
    delivery: CheckoutDeliveryDTO
    comment: string
    properties: PropertyDTO[]
    personType: CheckoutPersonTypeDTO
    profileId: string
    propIdsMap: PropIdsMap
    rules: { checked: boolean }
    coupon: CheckoutCouponDTO | null
    signedParameters: string
    siteId: string
    isAuthorized: boolean
}

export interface CheckoutPageDTO {
    checkout: CheckoutDTO
}

export type CheckoutApiResponse = PageData<CheckoutPageDTO>

export interface OrderConfirmResponse {
    redirectUrl?: string
    orderId?: number
    error?: string
}

export type OrderConfirmApiResponse = PageData<OrderConfirmResponse>
