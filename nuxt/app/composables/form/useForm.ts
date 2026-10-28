import type { ApiErrors } from '~/types/api'

interface UseFormOptions<T> {
    initialValues: T
    successMessage?: string
    onSubmit: (values: T) => Promise<void>
    validate?: (values: T) => ApiErrors | null
    onInvalid?: (errors: ApiErrors, fromServer?: boolean) => void
}

interface UseFormReturn<T> {
    values: T
    errors: Ref<ApiErrors>
    isLoading: Ref<boolean>
    isSuccess: Ref<boolean>
    handleSubmit: (e?: Event) => Promise<void>
    setFieldValue: (field: keyof T, value: any) => void
    setError: (field: string, message: string) => void
    clearErrors: () => void
    reset: () => void
}

function defaultOnInvalid(errors: ApiErrors, fromServer = false) {
    const keys = Object.keys(errors)
    if (keys.length === 1 && keys[0]) {
        const key = keys[0]
        useToast().error({ message: errors[key] })
    } else if (fromServer) {
        if (keys.length > 0) {
            for (const key in errors) {
                useToast().error({ message: errors[key] })
            }
        } else {
            useToast().error({ message: 'Произошла непредвиденная ошибка, попробуйте позже' })
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
    const values = reactive<T>({ ...initialValues }) as T
    const errors = ref<ApiErrors>({})
    const isLoading = ref(false)
    const isSuccess = ref(false)

    /**
     * Установка значения поля
     */
    const setFieldValue = (field: keyof T, value: any) => {
        values[field] = value
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
        clearErrors()

        if (validate) {
            const validationErrors = validate(values)
            if (validationErrors) {
                errors.value = validationErrors
                onInvalid(validationErrors)
                return
            }
        }

        isLoading.value = true
        try {
            await onSubmit(values)
            isSuccess.value = true
            useToast().success({ message: options.successMessage || 'Форма успешно отправлена' })
            Object.assign(values, initialValues)
            reset()
        } catch (error: any) {
            errors.value = processApiErrors(error)
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
