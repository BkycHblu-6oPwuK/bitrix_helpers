import type { CheckoutDTO } from '~/types/checkout'

/**
 * Логика типа плательщика в оформлении заказа: текущий тип,
 * признак юр. лица и переключение типа.
 */
export function useCheckoutPersonType(checkout: Ref<CheckoutDTO | null>, refresh: () => void) {
    const personType = computed(() => checkout.value?.personType)

    const isLegal = computed(() => checkout.value?.personType?.selected === 'legal')

    /**
     * Установка типа плательщика
     */
    const setPersonType = (code: string) => {
        if (checkout.value?.personType) {
            checkout.value.personType.oldPersonType = checkout.value.personType.selected
            checkout.value.personType.selected = code
            refresh()
        }
    }

    return {
        personType,
        isLegal,
        setPersonType,
    }
}
