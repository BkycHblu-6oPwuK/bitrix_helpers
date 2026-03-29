import { reactive, ref } from 'vue'
import { useApiFetch } from '~/composables/useApi'
import type { FormDTO, FormStoreResponse } from '~/types/web-form'
import { formatDate } from '~/utils/formatDate'

export function useDynamicForm(form: FormDTO) {
  const values = reactive<Record<string, any>>({})
  const errors = reactive<Record<string, string>>({})
  const loading = ref(false)

  const getKey = (name: string) =>
    form.formIdsMap?.[name] || name

  form.fields.forEach(field => {
    const key = getKey(field.name)

    values[key] =
      field.type === 'checkbox' ||
      (field.isMultiple && ['select', 'dropdown', 'multiselect'].includes(field.type))
        ? []
        : ''

    errors[key] = ''
  })

  const reset = () => {
    Object.keys(values).forEach(k => {
      values[k] = Array.isArray(values[k]) ? [] : ''
    })
    Object.keys(errors).forEach(k => (errors[k] = ''))
  }

  const buildFormData = () => {
    const fd = new FormData()

    for (const field of form.fields) {
      const key = getKey(field.name)
      let value = values[key]

      if (value === null || value === undefined || value === '') {
        continue
      }

      if (field.type === 'date') {
        fd.append(key, formatDate(value, form.dateFormat))
        continue
      }

      if (['file', 'image'].includes(field.type)) {
        if (Array.isArray(value)) {
          value.forEach(file => {
            if (file instanceof File) {
              fd.append(key, file)
            }
          })
        } else if (value instanceof File) {
          fd.append(key, value)
        }
        continue
      }

      if (Array.isArray(value)) {
        value.forEach(v => {
          fd.append(key, String(v))
        })
        continue
      }

      fd.append(key, String(value))
    }

    return fd
  }

  const submit = async () => {
    loading.value = true
    Object.keys(errors).forEach(k => (errors[k] = ''))

    try {
      const formData = buildFormData()

      const { data } = await useApiFetch<FormStoreResponse>(
        `/web-form/${form.id}`,
        {
          method: 'post',
          body: formData,
          contentType: 'multipart'
        }
      )

      const result = data?.page.form
      if (!result?.successAdded) {
        result?.fields?.forEach((f: any) => {
          const key = getKey(f.name)
          if (f.error) errors[key] = f.error
        })
        throw new Error('Ошибка валидации')
      }

      return data
    } finally {
      loading.value = false
    }
  }

  return {
    values,
    errors,
    loading,
    submit,
    reset,
    getKey
  }
}
