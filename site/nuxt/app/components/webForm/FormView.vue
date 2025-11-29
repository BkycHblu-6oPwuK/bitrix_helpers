<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useApi } from '~/composables/useApi'
import FormField from './FormField.vue'
import SubmitButton from '../ui/SubmitButton.vue'
import type { FormDTO, FormStoreRequest } from '~/types/webForm/form'

const props = defineProps<{ form: FormDTO }>()
const emit = defineEmits<{ (e: 'submitted', payload: any): void }>()
const toast = useToast()

const formValues = reactive<Record<string, any>>({})
const fieldErrors = reactive<Record<string, string>>({})
const loading = ref(false)

props.form.fields.forEach(f => {
  const key = props.form.formIdsMap[f.name] || f.name

  // Если поле множественного выбора (checkbox group, multiselect)
  if (f.type === 'checkbox' || (f.isMultiple && ['select', 'dropdown', 'multiselect'].includes(f.type))) {
    formValues[key] = []
  } else {
    formValues[key] = ''
  }

  fieldErrors[key] = ''
})

async function handleSubmit() {
  loading.value = true
  Object.keys(fieldErrors).forEach(k => (fieldErrors[k] = ''))

  try {
    const { data, error } = await useApi<FormStoreRequest>(
      `/web-form/${props.form.id}`,
      { method: 'POST', body: { ...formValues } }
    )

    if (error.value) throw error.value
    const response = data.value

    if (!response?.data?.form?.successAdded) {
      const form = response?.data?.form
      if (form?.fields) {
        form.fields.forEach((f: any) => {
          const key = form.formIdsMap?.[f.name] || f.name
          if (f.error) fieldErrors[key] = f.error
        })
      }
      throw new Error('Ошибка валидации')
    }

    toast.success({
      'message': 'Форма успешно отправлена'
    })
    emit('submitted', response)

    Object.keys(formValues).forEach(k => (formValues[k] = ''))
  } catch (e: any) {
    toast.error({
      'message': e?.message || 'Ошибка при отправке формы'
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-lg flex flex-col gap-4">
    <div v-if="form.title || form.description" class="mb-1">
      <h2 v-if="form.title" class="text-lg font-semibold text-gray-900">
        {{ form.title }}
      </h2>
      <p v-if="form.description" class="text-sm text-gray-500 mt-0.5">
        {{ form.description }}
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
      <FormField
        v-for="field in form.fields"
        :key="field.id"
        :field="field"
        v-model="formValues[form.formIdsMap[field.name] || field.name]"
        :error="fieldErrors[form.formIdsMap[field.name] || field.name]"
      />
      <SubmitButton :loading="loading" class="self-start">
        Отправить
      </SubmitButton>
    </form>
  </div>
</template>
