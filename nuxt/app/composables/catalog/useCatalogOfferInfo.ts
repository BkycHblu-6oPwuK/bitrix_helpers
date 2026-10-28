import type { CatalogItemDTO, CatalogOfferDTO, CatalogPriceDTO } from '~/types/iblock/catalog'
import type { PropertiesMap, PropertyItemDTO } from '~/types/iblock'

type ValueDTO = PropertyItemDTO['valuesDto'][number]

/** Базовая цена, иначе первая */
export const pickPrice = (prices?: CatalogPriceDTO[] | null): CatalogPriceDTO | null =>
    prices?.find(p => p.catalogGroup?.base) ?? prices?.[0] ?? null

/** Проверяет, заполнено ли значение свойства */
const isFilled = (val: ValueDTO) => {
    if (val.description?.trim()) return true
    const v = val.value
    if (v && typeof v === 'object' && Object.keys(v).length) return true
    return v !== null && v !== undefined && String(v).trim() !== ''
}

const hasValue = (prop?: PropertyItemDTO | null) => !!prop?.valuesDto?.some(isFilled)

/**
 * Цена, наличие и ID для корзины — ВСЕГДА от выбранного предложения.
 * (Раньше они считались по preselectedOffer / offers[0] и не менялись при выборе варианта.)
 */
export function useCatalogOfferInfo(
    item: MaybeRefOrGetter<CatalogItemDTO | null | undefined>,
    selectedOffer: MaybeRefOrGetter<CatalogOfferDTO | null | undefined>,
) {
    const price = computed(() => {
        const v = toValue(item)
        if (!v) return null

        const offer = toValue(selectedOffer)
        if (offer) return pickPrice(offer.prices)

        // простой товар → потом запасные варианты
        return pickPrice(v.prices)
            ?? pickPrice(v.preselectedOffer?.prices)
            ?? pickPrice(v.offers?.[0]?.prices)
    })

    /**
     * Итоговые свойства: свойства товара, поверх которых лежат НЕПУСТЫЕ свойства предложения.
     * Сливаем по каждому свойству отдельно, а не берём «или предложение, или товар» целиком.
     */
    const properties = computed<PropertiesMap>(() => {
        const base = toValue(item)?.properties ?? {}
        const offerProps = toValue(selectedOffer)?.properties
        if (!offerProps) return base

        const result: PropertiesMap = { ...base }
        for (const [code, prop] of Object.entries(offerProps)) {
            if (hasValue(prop)) result[code] = prop
        }
        return result
    })

    const isAvailable = computed(() => {
        const v = toValue(item)
        if (!v) return false

        const offer = toValue(selectedOffer)
        if (offer) return offer.catalog?.available ?? false
        if (v.offers?.length) return v.offers.some(o => o.catalog?.available)
        return v.catalog?.available ?? false
    })

    /**
     * ID, который уходит в корзину: предложение, а для простого товара — сам товар.
     * null — у товара есть предложения, но ни одно не выбрано.
     */
    const purchasableId = computed(() => {
        const v = toValue(item)
        if (!v) return null

        const offer = toValue(selectedOffer)
        if (offer) return offer.id
        return v.offers?.length ? null : v.id
    })

    return { price, isAvailable, purchasableId, properties }
}