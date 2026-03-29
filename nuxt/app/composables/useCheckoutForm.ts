import type { CheckoutFormDTO } from '~/types/checkout'

/**
 * Композабл для работы с формой оформления заказа
 */
export function useCheckoutForm() {
    const checkoutStore = useCheckoutStore()

    // Правила валидации
    const validateCheckoutForm = (values: CheckoutFormDTO & { 
        activePay: string
        deliveryId: string
        address: string
    }): Record<string, string> | null => {
        const errors: Record<string, string> = {}

        // Способ оплаты
        if (!values.activePay) {
            errors.activePay = 'Выберите способ оплаты'
        }

        // Способ доставки
        if (!values.deliveryId) {
            errors.deliveryId = 'Выберите способ доставки'
        }

        // Адрес доставки (только если не самовывоз)
        if (!values.address && !checkoutStore.isOwnDelivery) {
            errors.address = 'Укажите адрес доставки'
        }

        // Email
        if (!values.email) {
            errors.email = 'Введите email'
        } else if (!isValidEmail(values.email)) {
            errors.email = 'Введите корректный email'
        }

        // Телефон
        if (!values.phone) {
            errors.phone = 'Введите номер телефона'
        } else if (!isValidPhone(values.phone)) {
            errors.phone = 'Введите корректный номер телефона'
        }

        // ФИО
        if (!values.fio || values.fio.trim().length < 2) {
            errors.fio = 'Введите ФИО'
        }

        // Юридическое лицо
        if (checkoutStore.isLegal) {
            if (!values.legalName) {
                errors.legalName = 'Введите название компании'
            }
            if (!values.legalInn) {
                errors.legalInn = 'Введите ИНН'
            }
            if (!values.legalAddress) {
                errors.legalAddress = 'Введите юридический адрес'
            }
            if (!values.legalAddressCheck && !values.legalActualAddress) {
                errors.legalActualAddress = 'Введите фактический адрес'
            }
        }

        return Object.keys(errors).length > 0 ? errors : null
    }

    // Форма для основных данных
    const mainForm = useForm<CheckoutFormDTO & {
        activePay: string
        deliveryId: string
        address: string
    }>({
        initialValues: {
            email: checkoutStore.form?.email || '',
            phone: checkoutStore.form?.phone || '',
            fio: checkoutStore.form?.fio || '',
            legalName: checkoutStore.form?.legalName || '',
            legalInn: checkoutStore.form?.legalInn || '',
            legalAddress: checkoutStore.form?.legalAddress || '',
            legalAddressCheck: checkoutStore.form?.legalAddressCheck || false,
            legalActualAddress: checkoutStore.form?.legalActualAddress || '',
            activePay: checkoutStore.activePay || '',
            deliveryId: checkoutStore.delivery?.selectedId || '',
            address: checkoutStore.delivery?.address || '',
        },
        validate: validateCheckoutForm,
        onSubmit: async (values) => {
            // Обновляем данные в store
            checkoutStore.updateForm({
                email: values.email,
                phone: values.phone,
                fio: values.fio,
                legalName: values.legalName,
                legalInn: values.legalInn,
                legalAddress: values.legalAddress,
                legalAddressCheck: values.legalAddressCheck,
                legalActualAddress: values.legalActualAddress,
            })

            // Подтверждаем заказ
            await checkoutStore.confirm()
        },
    })

    // Синхронизация с store при изменении данных
    watch(() => checkoutStore.form, (newForm) => {
        if (newForm) {
            mainForm.values.email = newForm.email
            mainForm.values.phone = newForm.phone
            mainForm.values.fio = newForm.fio
            mainForm.values.legalName = newForm.legalName || ''
            mainForm.values.legalInn = newForm.legalInn || ''
            mainForm.values.legalAddress = newForm.legalAddress || ''
            mainForm.values.legalAddressCheck = newForm.legalAddressCheck || false
            mainForm.values.legalActualAddress = newForm.legalActualAddress || ''
        }
    }, { deep: true })

    watch(() => checkoutStore.activePay, (newPay) => {
        mainForm.values.activePay = newPay
    })

    watch(() => checkoutStore.delivery?.selectedId, (newDeliveryId) => {
        if (newDeliveryId) {
            mainForm.values.deliveryId = newDeliveryId
        }
    })

    watch(() => checkoutStore.delivery?.address, (newAddress) => {
        if (newAddress) {
            mainForm.values.address = newAddress
        }
    })

    return {
        mainForm,
        validateCheckoutForm,
    }
}

/**
 * Валидация email
 */
function isValidEmail(email: string): boolean {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
}

/**
 * Валидация телефона
 */
function isValidPhone(phone: string): boolean {
    // Простая проверка - минимум 10 цифр
    const digits = phone.replace(/\D/g, '')
    return digits.length >= 10
}
