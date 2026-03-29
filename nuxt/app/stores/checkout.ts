import { defineStore } from 'pinia'
import type {
    CheckoutDTO,
    CheckoutApiResponse,
    OrderConfirmApiResponse,
    CheckoutFormDTO,
    DeliveryMethodDTO
} from '~/types/checkout'

interface CheckoutState {
    checkout: CheckoutDTO | null
    loading: boolean
    initialized: boolean
}

export const useCheckoutStore = defineStore('checkout', {
    state: (): CheckoutState => ({
        checkout: null,
        loading: false,
        initialized: false,
    }),

    getters: {
        // Товары
        items: (state) => state.checkout?.items || [],

        // Доставка
        delivery: (state) => state.checkout?.delivery,

        selectedDelivery: (state): DeliveryMethodDTO | null => {
            if (!state.checkout?.delivery) return null
            const selectedId = state.checkout.delivery.selectedId
            return state.checkout.delivery.deliveries[selectedId] || null
        },

        isOwnDelivery: (state) =>
            state.checkout?.delivery?.deliveries[state.checkout.delivery.selectedId]?.isOwnDelivery || false,

        isStoreDelivery: (state) =>
            state.checkout?.delivery?.deliveries[state.checkout.delivery.selectedId]?.isStoreDelivery || false,

        isTransportDelivery: (state) =>
            state.checkout?.delivery?.deliveries[state.checkout.delivery.selectedId]?.isTransport || false,

        extraServices: (state) => {
            const delivery = state.checkout?.delivery
            if (!delivery) return []
            const selectedDelivery = delivery.deliveries[delivery.selectedId]
            return selectedDelivery?.extraServices?.filter(s => !s.isPriceService) || []
        },

        priceExtraService: (state) => {
            const delivery = state.checkout?.delivery
            if (!delivery) return null
            const selectedDelivery = delivery.deliveries[delivery.selectedId]
            return selectedDelivery?.extraServices?.find(s => s.isPriceService) || null
        },

        displayAddress: (state) => {
            const delivery = state.checkout?.delivery
            if (!delivery) return ''
            if (delivery.selectedPvz && delivery.address) {
                return `${delivery.city}, ${delivery.address}`
            }
            return delivery.city
        },

        // Оплата
        payments: (state) => state.checkout?.payments || {},
        activePay: (state) => state.checkout?.activePay || '',
        activePayId: (state) => {
            if (!state.checkout?.activePay || !state.checkout?.propIdsMap?.payments) return 0
            return state.checkout.propIdsMap.payments[state.checkout.activePay] || 0
        },

        // Цены
        totalPrice: (state) => state.checkout?.totalPrice,

        // Купон
        coupon: (state) => state.checkout?.coupon,

        // Тип плательщика
        personType: (state) => state.checkout?.personType,
        isLegal: (state) => state.checkout?.personType?.selected === 'legal',

        // Форма
        form: (state) => state.checkout?.form,
        comment: (state) => state.checkout?.comment || '',

        // Прочее
        profileId: (state) => state.checkout?.profileId || '',
    },

    actions: {
        async initialize() {
            if (this.initialized || this.loading) return

            try {
                this.loading = true

                const { data } = await useApiFetch<CheckoutApiResponse>(
                    '/checkout/get',
                    {
                        method: 'get',
                    }
                )

                if (data?.page?.checkout) {
                    this.setCheckoutDTO(data.page.checkout)
                    this.initialized = true
                }
            } catch (e) {
                console.error('Checkout refresh error:', e)
                useToast().error({ message: 'Ошибка обновления данных заказа' })
            } finally {
                this.loading = false
            }
        },

        /**
         * Обновление данных checkout из DTO
         */
        setCheckoutDTO(checkoutDTO: CheckoutDTO) {
            this.checkout = checkoutDTO
        },

        /**
         * Обновление checkout с сервера
         */
        async refresh() {
            if (!this.checkout || this.loading) return

            try {
                this.loading = true

                const formData = this.buildOrderParams()

                const { data } = await useApiFetch<CheckoutApiResponse>(
                    '/order/refresh',
                    {
                        method: 'post',
                        body: formData,
                    }
                )

                if (data?.page?.checkout) {
                    this.setCheckoutDTO(data.page.checkout)
                }
            } catch (e) {
                console.error('Checkout refresh error:', e)
                useToast().error({ message: 'Ошибка обновления данных заказа' })
            } finally {
                this.loading = false
            }
        },

        /**
         * Подтверждение заказа
         */
        async confirm() {
            if (!this.checkout || this.loading) return

            try {
                this.loading = true

                const formData = this.buildOrderParams()

                const { data } = await useApiFetch<OrderConfirmApiResponse>(
                    '/checkout/create',
                    {
                        method: 'post',
                        body: formData,
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
                this.loading = false
            }
        },

        /**
         * Установка выбранного способа доставки
         */
        setDeliveryId(deliveryId: string) {
            if (this.checkout?.delivery) {
                this.checkout.delivery.selectedId = deliveryId
                this.refresh()
            }
        },

        /**
         * Установка выбранного способа оплаты
         */
        setPaymentId(paymentId: string) {
            if (this.checkout) {
                this.checkout.activePay = paymentId
                this.refresh()
            }
        },

        /**
         * Установка адреса доставки
         */
        setAddress(address: string) {
            if (this.checkout?.delivery) {
                this.checkout.delivery.address = address
            }
        },

        /**
         * Установка города
         */
        setCity(city: string) {
            if (this.checkout?.delivery) {
                this.checkout.delivery.city = city
            }
        },

        /**
         * Установка координат
         */
        setCoordinates(coordinates: [number, number]) {
            if (this.checkout?.delivery) {
                this.checkout.delivery.coordinates = coordinates
            }
        },

        /**
         * Установка ПВЗ
         */
        setPvz(pvzId: string) {
            if (this.checkout?.delivery) {
                this.checkout.delivery.selectedPvz = pvzId
            }
        },

        /**
         * Установка выбранного магазина
         */
        setStore(storeId: number) {
            if (this.checkout?.delivery) {
                this.checkout.delivery.storeSelectedId = storeId
            }
        },

        /**
         * Установка даты доставки
         */
        setCompletionDate(date: string) {
            if (this.checkout?.delivery) {
                this.checkout.delivery.completionDate = date
            }
        },

        /**
         * Установка значения дополнительной услуги
         */
        setExtraServiceValue(serviceId: string, value: string | number) {
            const delivery = this.selectedDelivery
            if (!delivery?.extraServices) return

            const service = delivery.extraServices.find(s => s.id === serviceId)
            if (service) {
                service.value = value
            }
        },

        /**
         * Установка комментария
         */
        setComment(comment: string) {
            if (this.checkout) {
                this.checkout.comment = comment
            }
        },

        /**
         * Установка типа плательщика
         */
        setPersonType(code: string) {
            if (this.checkout?.personType) {
                this.checkout.personType.oldPersonType = this.checkout.personType.selected
                this.checkout.personType.selected = code
                this.refresh()
            }
        },

        /**
         * Обновление полей формы
         */
        updateForm(form: Partial<CheckoutFormDTO>) {
            if (this.checkout?.form) {
                this.checkout.form = { ...this.checkout.form, ...form }
            }
        },

        /**
         * Сброс локации
         */
        resetLocation() {
            if (this.checkout?.delivery) {
                this.checkout.delivery.selectedPvz = ''
                this.checkout.delivery.address = ''
                this.checkout.delivery.city = ''
                this.checkout.delivery.coordinates = [0, 0]
            }
        },

        /**
         * Построение параметров заказа для отправки
         */
        buildOrderParams(): Record<string, any> {
            if (!this.checkout) return {}

            const { form, delivery, personType, propIdsMap, activePay, comment, profileId } = this.checkout

            const params: Record<string, any> = {
                profileId,
                personType: personType.selected,
                personTypeOld: personType.oldPersonType,
                email: form.email,
                phone: form.phone,
                fio: form.fio,
                comment,
                paymentId: activePay,
                deliveryId: delivery.selectedId,
                city: delivery.city,
                location: delivery.location,
                address: delivery.address,
                postCode: delivery.postCode,
                coordinates: delivery.coordinates.join(','),
            }

            // Юридическое лицо
            if (personType.selected === 'legal') {
                params.legalName = form.legalName
                params.legalInn = form.legalInn
                params.legalAddress = form.legalAddress
                params.legalAddressCheck = form.legalAddressCheck
                params.legalActualAddress = form.legalActualAddress
            }

            // Выбранный магазин
            if (delivery.storeSelectedId) {
                params.storeId = delivery.storeSelectedId
            }

            // ПВЗ
            if (delivery.selectedPvz) {
                params.pvzId = delivery.selectedPvz
            }

            // Дата доставки
            if (delivery.completionDate) {
                params.completionDate = delivery.completionDate
            }

            // Дополнительные услуги
            const selectedDelivery = delivery.deliveries[delivery.selectedId]
            if (selectedDelivery?.extraServices) {
                params.extraServices = {}
                selectedDelivery.extraServices.forEach(service => {
                    if (service.value) {
                        params.extraServices[service.id] = service.value
                    }
                })
            }

            return params
        },
    },
})
