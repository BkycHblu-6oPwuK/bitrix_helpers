import type { Ref } from 'vue'

interface UseFormOptions<T> {
  initialValues: T
  onSubmit: (values: T) => Promise<void>
  validate?: (values: T) => Record<string, string> | null
}

interface UseFormReturn<T> {
  values: T
  errors: Ref<Record<string, string>>
  isLoading: Ref<boolean>
  isValid: Ref<boolean>
  handleSubmit: (e?: Event) => Promise<void>
  setFieldValue: (field: keyof T, value: any) => void
  setError: (field: string, message: string) => void
  clearErrors: () => void
  reset: () => void
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
  const { initialValues, onSubmit, validate } = options

  // Реактивные значения формы
  const values = reactive<T>({ ...initialValues }) as T
  
  // Ошибки валидации
  const errors = ref<Record<string, string>>({})
  
  // Состояние загрузки
  const isLoading = ref(false)
  
  // Валидность формы
  const isValid = computed(() => {
    if (validate) {
      const validationErrors = validate(values)
      return !validationErrors || Object.keys(validationErrors).length === 0
    }
    return true
  })

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
    if (e) {
      e.preventDefault()
    }

    // Очищаем предыдущие ошибки
    clearErrors()

    // Валидация
    if (validate) {
      const validationErrors = validate(values)
      if (validationErrors) {
        errors.value = validationErrors
        return
      }
    }

    // Отправка
    isLoading.value = true
    try {
      await onSubmit(values)
    } catch (error: any) {
      // Обработка ошибок API
      if (error.data?.errors) {
        // Формат ошибок от API: { field: 'message' }
        errors.value = error.data.errors
      } else {
        // Общая ошибка
        errors.value = { _general: error.message || 'Произошла ошибка' }
      }
    } finally {
      isLoading.value = false
    }
  }

  return {
    values,
    errors,
    isLoading,
    isValid,
    handleSubmit,
    setFieldValue,
    setError,
    clearErrors,
    reset,
  }
}
