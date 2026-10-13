import type { CheckoutDTO, DeliveryDTO } from '~/types/checkout'

/**
 * Логика доставки в оформлении заказа: выбранный способ, флаги типа доставки,
 * доп. услуги, адрес и методы их изменения.
 */
export function useCheckoutDelivery(checkout: Ref<CheckoutDTO | null>, refresh: () => void) {
    const delivery = computed(() => checkout.value?.delivery)

    const selectedDelivery = computed<DeliveryDTO | null>(() => {
        if (!checkout.value?.delivery) return null
        const selectedId = checkout.value.delivery.selectedId
        return checkout.value.delivery.deliveries[selectedId] || null
    })

    const isStoreDelivery = computed(() => selectedDelivery.value?.isStoreDelivery || false)
    const isCourierDelivery = computed(() => selectedDelivery.value?.isCourier || false)
    const isPickupDelivery = computed(() => selectedDelivery.value?.isPickup || false)

    const extraServices = computed(() => selectedDelivery.value?.extraServices || [])

    const displayAddress = computed(() => {
        const d = checkout.value?.delivery
        if (!d) return ''
        if (d.selectedPvz && d.address) {
            return `${d.city}, ${d.address}`
        }
        return d.city
    })

    /**
     * Установка выбранного способа доставки
     */
    const setDeliveryId = (deliveryId: string | number) => {
        if (checkout.value?.delivery) {
            checkout.value.delivery.selectedId = Number(deliveryId)
            refresh()
        }
    }

    /**
     * Установка адреса доставки
     */
    const setAddress = (address: string) => {
        if (checkout.value?.delivery) {
            checkout.value.delivery.address = address
        }
    }

    /**
     * Установка города
     */
    const setCity = (city: string) => {
        if (checkout.value?.delivery) {
            checkout.value.delivery.city = city
        }
    }

    /**
     * Установка ПВЗ
     */
    const setPvz = (pvzId: string) => {
        if (checkout.value?.delivery) {
            checkout.value.delivery.selectedPvz = pvzId
        }
    }

    /**
     * Установка выбранного магазина
     */
    const setStore = (storeId: number) => {
        if (checkout.value?.delivery) {
            checkout.value.delivery.storeSelectedId = storeId
        }
    }

    /**
     * Установка значения дополнительной услуги
     */
    const setExtraServiceValue = (serviceId: string, value: string | number) => {
        const service = selectedDelivery.value?.extraServices?.find(s => s.id === serviceId)
        if (service) {
            service.value = value
        }
    }

    /**
     * Сброс локации
     */
    const resetLocation = () => {
        if (checkout.value?.delivery) {
            checkout.value.delivery.selectedPvz = ''
            checkout.value.delivery.address = ''
            checkout.value.delivery.city = ''
            checkout.value.delivery.location = ''
        }
    }

    return {
        delivery,
        selectedDelivery,
        isStoreDelivery,
        isCourierDelivery,
        isPickupDelivery,
        extraServices,
        displayAddress,
        setDeliveryId,
        setAddress,
        setCity,
        setPvz,
        setStore,
        setExtraServiceValue,
        resetLocation,
    }
}
