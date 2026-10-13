import type { CheckoutDTO } from '~/types/checkout'

/**
 * Логика оплаты в оформлении заказа: доступные системы оплаты,
 * активная система и её числовой id.
 */
export function useCheckoutPayment(checkout: Ref<CheckoutDTO | null>, refresh: () => void) {
    const payments = computed(() => checkout.value?.payments || {})

    const activePay = computed(() => checkout.value?.activePay || '')

    const activePayId = computed(() => {
        if (!checkout.value?.activePay || !checkout.value?.propIdsMap?.payments) return 0
        return checkout.value.propIdsMap.payments[checkout.value.activePay] || 0
    })

    /**
     * Установка выбранного способа оплаты
     */
    const setPaymentId = (paymentId: string) => {
        if (checkout.value) {
            checkout.value.activePay = paymentId
            refresh()
        }
    }

    return {
        payments,
        activePay,
        activePayId,
        setPaymentId,
    }
}
