/**
 * Типы оформления заказа
 */

import type { PageData } from "./page"

export interface CheckoutItemDTO {
    id: number
    name: string
    quantity: number
    price: number
    priceFormatted: string
    image: string
    url: string
}

export interface DeliveryMethodDTO {
    id: string
    name: string
    description: string
    price: number
    priceFormatted: string
    isOwnDelivery: boolean
    isStoreDelivery: boolean
    isTransport: boolean
    extraServices: ExtraServiceDTO[]
    stores?: StoreDTO[]
}

export interface ExtraServiceDTO {
    id: string
    title: string
    price: number
    priceFormatted: string
    isPriceService: boolean
    value?: string | number
    values?: ExtraServiceValueDTO[]
}

export interface ExtraServiceValueDTO {
    id: number
    title: string
}

export interface StoreDTO {
    id: number
    title: string
    address: string
    phone: string
    schedule: string
    coordinates: [number, number]
}

export interface PaymentMethodDTO {
    id: string
    name: string
    description: string
    logo: string
}

export interface PersonTypeDTO {
    code: string
    name: string
    sort: number
}

export interface CheckoutFormDTO {
    email: string
    phone: string
    fio: string
    legalName?: string
    legalInn?: string
    legalAddress?: string
    legalAddressCheck?: boolean
    legalActualAddress?: string
}

export interface CheckoutDeliveryDTO {
    selectedId: string
    city: string
    location: string
    address: string
    postCode: string
    coordinates: [number, number]
    selectedPvz?: string
    completionDate?: string
    storeSelectedId?: number
    distance: number
    duration: number
    deliveries: Record<string, DeliveryMethodDTO>
}

export interface CheckoutTotalPriceDTO {
    basket: number
    basketFormatted: string
    delivery: number
    deliveryFormatted: string
    discount: number
    discountFormatted: string
    total: number
    totalFormatted: string
}

export interface CheckoutPersonTypeDTO {
    selected: string
    oldPersonType: string
    types: Record<string, PersonTypeDTO>
}

export interface CheckoutCouponDTO {
    code: string
    discount: number
    discountFormatted: string
}

export interface CheckoutDTO {
    items: CheckoutItemDTO[]
    delivery: CheckoutDeliveryDTO
    payments: Record<string, PaymentMethodDTO>
    activePay: string
    totalPrice: CheckoutTotalPriceDTO
    coupon: CheckoutCouponDTO | null
    personType: CheckoutPersonTypeDTO
    form: CheckoutFormDTO
    rules: Record<string, any>
    propIdsMap: Record<string, any>
    profileId: string
    comment: string
    siteId: string
    signedParams: string
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
