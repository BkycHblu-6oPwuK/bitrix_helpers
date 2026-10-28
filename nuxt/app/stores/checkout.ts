import { defineStore } from 'pinia'
import type {
    CheckoutDTO,
    CheckoutApiResponse,
    OrderConfirmApiResponse,
} from '~/types/pages/checkout'

/**
 * Store оформления заказа.
 * Данные раскладываются по композаблам (доставка, оплата, тип плательщика),
 * а store хранит корневой DTO, флаги загрузки и работу с API.
 */
export const useCheckoutStore = defineStore('checkout', () => {
    const checkout = ref<CheckoutDTO | null>(null)
    const loading = ref(false)
    const initialized = ref(false)

    /**
     * Обновление данных checkout из DTO
     */
    const setCheckoutDTO = (checkoutDTO: CheckoutDTO) => {
        checkout.value = checkoutDTO
    }

    /**
     * Обновление checkout с сервера
     */
    async function refresh() {
        if (!checkout.value || loading.value) return

        try {
            loading.value = true

            const { data } = await useApiFetch<CheckoutApiResponse>(
                '/order/refresh',
                {
                    method: 'post',
                    body: buildOrderParams(),
                }
            )

            if (data?.page?.checkout) {
                setCheckoutDTO(data.page.checkout)
            }
        } catch (e) {
            console.error('Checkout refresh error:', e)
            useToast().error({ message: 'Ошибка обновления данных заказа' })
        } finally {
            loading.value = false
        }
    }

    // Слои оформления
    const delivery = useCheckoutDelivery(checkout, refresh)
    const payment = useCheckoutPayment(checkout, refresh)
    const person = useCheckoutPersonType(checkout, refresh)

    // Простые производные значения
    const items = computed(() => checkout.value?.items || [])
    const total = computed(() => checkout.value?.total)
    const coupon = computed(() => checkout.value?.coupon)
    const properties = computed(() => checkout.value?.properties || [])
    const comment = computed(() => checkout.value?.comment || '')
    const profileId = computed(() => checkout.value?.profileId || '')

    /**
     * Установка комментария
     */
    const setComment = (value: string) => {
        if (checkout.value) {
            checkout.value.comment = value
        }
    }

    /**
     * Построение параметров заказа для отправки
     */
    const buildOrderParams = () => {
        const formData = new URLSearchParams()
        if (!checkout.value) return formData

        const {
            propIdsMap,
            personType,
            delivery: deliveryData,
            properties = [],
            comment,
            activePay,
            profileId,
            signedParameters,
        } = checkout.value

        const propsMap = propIdsMap.properties || {}
        delivery.setAddress(delivery.displayAddress.value)

        // Подписанные параметры компонента
        formData.append('signedParamsString', signedParameters)

        // Профиль
        formData.append(propIdsMap.profileId, profileId)

        // Тип плательщика (отдаётся числовой id из personTypes)
        formData.append(propIdsMap.personType, String(propIdsMap.personTypes?.[personType.selected] ?? ''))
        formData.append(propIdsMap.personTypeOld, String(propIdsMap.personTypes?.[personType.oldPersonType] ?? ''))

        // Свойства заказа: ORDER_PROP_X = value
        properties.forEach((prop) => {
            const key = propsMap[prop.code]
            if (key) {
                formData.append(key, prop.value ?? '')
            }
        })

        // Комментарий к заказу
        if (propIdsMap.comment) {
            formData.append(propIdsMap.comment, comment ?? '')
        }

        // Система оплаты
        if (activePay && propIdsMap.payments?.[activePay]) {
            formData.append(propIdsMap.paySystem, String(propIdsMap.payments[activePay]))
        }

        // Доставка
        formData.append(propIdsMap.delivery, String(deliveryData.selectedId))

        // Дополнительные услуги доставки
        const selectedDelivery = deliveryData.deliveries?.[deliveryData.selectedId]
        const extraServices = selectedDelivery?.extraServices || []
        extraServices.forEach((service) => {
            if (service.value) {
                formData.append(
                    `${propIdsMap.extraServices}[${deliveryData.selectedId}][${service.id}]`,
                    String(service.value),
                )
            }
        })

        // Самовывоз из магазина
        if (deliveryData.storeSelectedId > 0) {
            formData.append(propIdsMap.shopId, String(deliveryData.storeSelectedId))
        }

        // Тип передачи локации
        formData.append(propIdsMap.locationType, 'code')

        return formData
    }

    /**
     * Первичная загрузка данных оформления
     */
    async function initialize() {
        if (initialized.value || loading.value) return

        try {
            loading.value = true

            const { data } = await useApiFetch<CheckoutApiResponse>(
                '/checkout/get',
                {
                    method: 'get',
                }
            )

            if (data?.page?.checkout) {
                setCheckoutDTO(data.page.checkout)
                initialized.value = true
            }
        } catch (e) {
            console.error('Checkout refresh error:', e)
            useToast().error({ message: 'Ошибка обновления данных заказа' })
        } finally {
            loading.value = false
        }
    }

    /**
     * Подтверждение заказа
     */
    async function confirm() {
        if (!checkout.value || loading.value) return

        try {
            loading.value = true

            const { data } = await useApiFetch<OrderConfirmApiResponse>(
                '/checkout/create',
                {
                    method: 'post',
                    body: buildOrderParams(),
                }
            )

            if (data?.page?.redirectUrl) {
                window.location.href = data.page.redirectUrl
            } else if (data?.page?.error) {
                useToast().error({ message: data.page.error })
            }
        } catch (e: any) {
            console.error('Order confirm error:', e)
            useToast().error({ message: e.message || 'Ошибка при оформлении заказа' })
        } finally {
            loading.value = false
        }
    }

    return {
        // Состояние
        checkout,
        loading,
        initialized,

        // Производные значения
        items,
        total,
        coupon,
        properties,
        comment,
        profileId,

        // Слои оформления
        ...delivery,
        ...payment,
        ...person,

        // Действия
        setCheckoutDTO,
        setComment,
        buildOrderParams,
        initialize,
        refresh,
        confirm,
    }
})
