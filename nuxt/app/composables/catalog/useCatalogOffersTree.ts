import type { CatalogItemDTO, CatalogOfferDTO } from "~/types/iblock/catalog"

/**
 * Композабл для работы с картой торговых предложений товара
 * Предоставляет методы для выбора значений свойств и получения доступных вариантов
 */
export function useCatalogOffersTree(item: Ref<CatalogItemDTO | null>) {
    const selectedOffer = ref<CatalogOfferDTO | null>(null)
    const selectedValues = ref<Record<string, string>>({})

    /**
     * Инициализация выбранных значений свойств на основе предвыбранного предложения или первого доступного варианта
     */
    const initializeSelectedValues = () => {
        if (!item.value) return

        if (selectedOffer.value && item.value.offersTree) {
            const offerEntry = Object.entries(item.value.offersTree.map).find(
                ([offerId]) => Number(offerId) === selectedOffer.value?.id
            )

            if (offerEntry) {
                selectedValues.value = { ...offerEntry[1] }
            }
        } else if (item.value.offersTree?.props.length > 0) {
            item.value.offersTree.props.forEach(prop => {
                if (prop.values.length > 0 && prop.values[0]) {
                    selectedValues.value[prop.code] = prop.values[0].value
                }
            })
            findAndSelectOffer()
        }
    }

    /**
     * Поиск и установка предложения на основе текущих выбранных значений свойств
     */
    const findAndSelectOffer = () => {
        if (!item.value?.offersTree) return

        const offerEntry = Object.entries(item.value.offersTree.map).find(([_, values]) => {
            return Object.keys(selectedValues.value).every(
                key => values[key] === selectedValues.value[key]
            )
        })

        if (offerEntry) {
            const offerId = Number(offerEntry[0])
            const offer = item.value.offers.find(o => o.id === offerId)
            selectedOffer.value = offer || null
        } else {
            selectedOffer.value = null
        }
    }

    /**
     * Выбор значения свойства торгового предложения
     */
    const selectOfferValue = (propCode: string, value: string) => {
        selectedValues.value[propCode] = value
        findAndSelectOffer()
    }

    /**
     * Получение доступных значений для свойства на основе текущих выбранных значений других свойств
     */
    const getAvailableValuesForProp = (propCode: string) => {
        if (!item.value?.offersTree) return []

        const prop = item.value.offersTree.props.find(p => p.code === propCode)
        if (!prop) return []

        const availableValues = new Set<string>()

        Object.entries(item.value.offersTree.map).forEach(([offerId, offerValues]) => {
            const otherPropsMatch = Object.keys(selectedValues.value)
                .filter(key => key !== propCode)
                .every(key => offerValues[key] === selectedValues.value[key])

            if (otherPropsMatch) {
                const offer = item.value!.offers.find(o => o.id === Number(offerId))
                if (offer?.catalog.available) {
                    availableValues.add(offerValues[propCode])
                }
            }
        })

        return prop.values.filter(valueItem => availableValues.has(valueItem.value))
    }

    return {
        selectedOffer,
        selectedValues,
        initializeSelectedValues,
        selectOfferValue,
        getAvailableValuesForProp
    }
}
