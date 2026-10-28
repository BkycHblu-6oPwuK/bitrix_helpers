import type { InjectionKey } from 'vue'
import type { CatalogItemDTO } from '~/types/iblock/catalog'

interface CatalogProductOptions {
    /** Писать выбранное предложение в адрес как ?offer=ID (можно делиться ссылкой) */
    syncOfferUrl?: boolean
}

export type CatalogProductContext = ReturnType<typeof createCatalogProduct>

const catalogProductKey: InjectionKey<CatalogProductContext> = Symbol('catalogProduct')

/**
 * Состояние детальной карточки: товар, выбранное предложение, цена, наличие, количество.
 * Создаётся один раз на странице, дочерние компоненты получают его через inject.
 * Замена Pinia-стору: не нужны setItem/reset, нет рассинхрона при переходах между товарами.
 */
function createCatalogProduct(source: MaybeRefOrGetter<CatalogItemDTO>, options: CatalogProductOptions = {}) {
    const route = useRoute()
    const item = computed(() => toValue(source))

    const itemData = useCatalogItem(item)
    const offers = useCatalogOffersTree(item, {
        initialOfferId: () => Number(route.query.offer) || null,
    })
    const offerInfo = useCatalogOfferInfo(item, offers.selectedOffer)

    const { quantity, incrementQuantity, decrementQuantity, setQuantity } = useQuantity()
    watch(() => item.value.id, () => { quantity.value = 1 })

    const selectOfferValue = (propCode: string, value: string) => {
        offers.selectOfferValue(propCode, value)

        const id = offers.selectedOffer.value?.id
        if (!options.syncOfferUrl || !import.meta.client || !id) return

        // history напрямую, а не router.replace — чтобы не перезапускать загрузку страницы
        const url = new URL(window.location.href)
        url.searchParams.set('offer', String(id))
        window.history.replaceState(window.history.state, '', url)
    }

    return {
        item,
        ...itemData,
        ...offers,
        ...offerInfo,
        selectOfferValue,
        quantity,
        incrementQuantity,
        decrementQuantity,
        setQuantity,
    }
}

/** Вызывать один раз в компоненте страницы товара */
export function provideCatalogProduct(item: MaybeRefOrGetter<CatalogItemDTO> | null | undefined, options?: CatalogProductOptions) {
    if (!item) throw new Error('provideCatalogProduct() вызван с пустым item')
    const ctx = createCatalogProduct(item, options)
    provide(catalogProductKey, ctx)
    return ctx
}

/** Вызывать в дочерних компонентах карточки */
export function useCatalogProduct() {
    const ctx = inject(catalogProductKey)
    if (!ctx) throw new Error('useCatalogProduct() вызван вне карточки товара: нет provideCatalogProduct()')
    return ctx
}