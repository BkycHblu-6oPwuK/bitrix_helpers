interface UseFormOptions<T> {
    initialValues: T
    successMessage?: string
    onSubmit: (values: T) => Promise<void>
    validate?: (values: T) => Record<string, string> | null
    onInvalid?: (errors: Record<string, string>, fromServer?: boolean) => void
}

interface UseFormReturn<T> {
    values: T
    errors: Ref<Record<string, string>>
    isLoading: Ref<boolean>
    isSuccess: Ref<boolean>
    handleSubmit: (e?: Event) => Promise<void>
    setFieldValue: (field: keyof T, value: any) => void
    setError: (field: string, message: string) => void
    clearErrors: () => void
    reset: () => void
}

function processApiErrors(errorData: any): Record<string, string> {
    const errors: Record<string, string> = {}
    for (const err of errorData) {
        if (err.code) {
            errors[err.code] = err.message
        } else {
            errors._general = err.message
        }
    }
    return errors
}

function defaultOnInvalid(errors: Record<string, string>, fromServer = false) {
    const keys = Object.keys(errors)
    if (keys.length === 1 && keys[0]) {
        const key = keys[0]
        useToast().error({ message: errors[key] })
    } else if (fromServer) {
        for (const key in errors) {
            useToast().error({ message: errors[key] })
        }
    } else {
        useToast().error({ message: 'Все поля обязательны для заполнения' })
    }
}

/**
 * Универсальный композабл для работы с формами
 * Обеспечивает валидацию, обработку ошибок, состояние загрузки
 * 
 * @example
 * const { values, errors, isLoading, handleSubmit } = useForm({
 *   initialValues: { email: '', password: '' },
 *   onSubmit: async (values) => {
 *     await api.login(values)
 *   },
 *   validate: (values) => {
 *     if (!values.email) return { email: 'Email обязателен' }
 *     return null
 *   }
 * })
 */
export function useForm<T extends Record<string, any>>(
    options: UseFormOptions<T>
): UseFormReturn<T> {
    const { initialValues, onSubmit, validate, onInvalid: userOnInvalid } = options
    const onInvalid = userOnInvalid || defaultOnInvalid

    // Реактивные значения формы
    const values = reactive<T>({ ...initialValues }) as T

    // Ошибки валидации
    const errors = ref<Record<string, string>>({})

    // Состояние загрузки
    const isLoading = ref(false)

    const isSuccess = ref(false)

    /**
     * Установка значения поля
     */
    const setFieldValue = (field: keyof T, value: any) => {
        values[field] = value
        // Очищаем ошибку для этого поля при изменении
        if (errors.value[field as string]) {
            delete errors.value[field as string]
        }
    }

    /**
     * Установка ошибки для поля
     */
    const setError = (field: string, message: string) => {
        errors.value[field] = message
    }

    /**
     * Очистка всех ошибок
     */
    const clearErrors = () => {
        errors.value = {}
    }

    /**
     * Сброс формы к начальным значениям
     */
    const reset = () => {
        Object.assign(values, initialValues)
        clearErrors()
    }

    /**
     * Обработка отправки формы
     */
    const handleSubmit = async (e?: Event) => {
        if (isLoading.value) return
        if (e) {
            e.preventDefault()
        }
        isSuccess.value = false

        // Очищаем предыдущие ошибки
        clearErrors()

        // Валидация
        if (validate) {
            const validationErrors = validate(values)
            if (validationErrors) {
                errors.value = validationErrors
                onInvalid(validationErrors)
                return
            }
        }

        // Отправка
        isLoading.value = true
        try {
            await onSubmit(values)
            isSuccess.value = true
            useToast().success({ message: options.successMessage || 'Форма успешно отправлена' })
            Object.assign(values, initialValues)
            reset()
        } catch (error: any) {
            if (error.data?.errors) {
                errors.value = processApiErrors(error.data.errors)
            } else if (error.cause) {
                errors.value = typeof error.cause === 'object' ? processApiErrors(error.cause) : { _general: String(error.cause) }
            } else {
                errors.value = { _general: error.message || 'Произошла ошибка' }
            }
            onInvalid(errors.value, true)
        } finally {
            isLoading.value = false
        }
    }

    return {
        values,
        errors,
        isLoading,
        isSuccess,
        handleSubmit,
        setFieldValue,
        setError,
        clearErrors,
        reset,
    }
}
