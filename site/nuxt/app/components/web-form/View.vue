<script setup lang="ts">
import { useDynamicForm } from '~/composables/useDynamicForm'
import type { FormDTO } from '~/types/web-form';

const props = defineProps<{ form: FormDTO }>()
const emit = defineEmits<{ (e: 'submitted', payload: any): void }>()
const toast = useToast()

const {
  values,
  errors,
  loading,
  submit,
  reset,
  getKey
} = useDynamicForm(props.form)
async function handleSubmit() {
  try {
    const response = await submit()

    toast.success({ message: 'Форма успешно отправлена' })
    emit('submitted', response)
    reset()
  } catch (e: any) {
    toast.error({ message: e?.message || 'Ошибка при отправке формы' })
  }
}
</script>

<template>
  <div class="w-full max-w-lg flex flex-col gap-4">
    <div v-if="form.title || form.description">
      <h2 v-if="form.title" class="text-lg font-semibold">
        {{ form.title }}
      </h2>
      <p v-if="form.description" class="text-sm text-gray-500">
        {{ form.description }}
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
      <WebFormField
        v-for="field in form.fields"
        :key="field.id"
        :field="field"
        v-model="values[getKey(field.name)]"
        :error="errors[getKey(field.name)]"
      />

      <UiSubmitButton :loading="loading">
        Отправить
      </UiSubmitButton>
    </form>
  </div>
</template>
