import { defineStore } from 'pinia'
import type { CatalogItemDTO } from '~/types/iblock/catalog'

/**
 * Store для управления деталями элемента каталога
 * Хранит данные элемента, выбранное торговое предложение, количество и предоставляет методы для управления ими
 */
export const useCatalogDetailStore = defineStore('catalogDetail', () => {
    const item = ref<CatalogItemDTO | null>(null)
    const { isAvailable, images, price } = useCatalogItem(item)
    const { selectedOffer, selectedValues, initializeSelectedValues, selectOfferValue, getAvailableValuesForProp } = useCatalogOffersTree(item)
    const { quantity, incrementQuantity, decrementQuantity, setQuantity } = useQuantity()

    /**
     * Устанавливает новый элемент каталога и инициализирует связанные данные
     */
    const setItem = (newItem: CatalogItemDTO) => {
        item.value = newItem
        selectedOffer.value = newItem.preselectedOffer
        quantity.value = 1
        selectedValues.value = {}
        initializeSelectedValues()
    }

    /**
     * Сбрасывает состояние стора к первоначальному виду
     */
    const reset = () => {
        item.value = null
        selectedOffer.value = null
        selectedValues.value = {}
        quantity.value = 1
    }

    return {
        item,
        selectedOffer,
        selectedValues,
        quantity,

        isAvailable,
        images,
        price,

        setItem,
        reset,
        selectOfferValue,
        getAvailableValuesForProp,
        incrementQuantity,
        decrementQuantity,
        setQuantity,
    }
})
