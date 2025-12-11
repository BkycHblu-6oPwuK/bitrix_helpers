<script setup lang="ts">
const emit = defineEmits<{
  success: []
  switchToEmail: []
}>()

const authStore = useAuthStore()

type Step = 'phone' | 'verification'

const step = ref<Step>('phone')

const phoneForm = useForm({
  initialValues: { phone: '' },
  validate: (values) => (!values.phone ? { phone: 'Введите номер телефона' } : null),
  onSubmit: async (values) => {
    const { useApiFetch } = await import('~/composables/useApi')
    await useApiFetch('user/login', {
      method: 'post',
      body: { type: 'phone', phone: values.phone }
    })
    step.value = 'verification'
  }
})

const codeForm = useForm({
  initialValues: { code: '' },
  validate: (values) => (!values.code ? { code: 'Код обязателен' } : null),
  onSubmit: async (values) => {
    const { useApiFetch } = await import('~/composables/useApi')
    const response = await useApiFetch('user/login', {
      method: 'post',
      body: {
        type: 'phone',
        phone: phoneForm.values.phone,
        codeVerify: values.code,
      }
    })
    if (response.status === 'success' && response.data) {
      authStore.setAuthData(response.data)
      emit('success')
    }
  }
})

const reset = () => {
  step.value = 'phone'
  phoneForm.reset()
  codeForm.reset()
}

const clearErrors = () => {
  phoneForm.clearErrors()
  codeForm.clearErrors()
}

defineExpose({ reset, clearErrors })
</script>

<template>
  <div>
    <form v-if="step === 'phone'" @submit="phoneForm.handleSubmit" class="space-y-5">
      <UAlert v-if="phoneForm.errors.value._general" color="error" variant="soft" :title="phoneForm.errors.value._general" class="mb-4" />

      <UFormField label="Телефон" :error="phoneForm.errors.value.phone">
        <UInput 
          placeholder="+7 (999) 999-99-99" 
          class="w-full" 
          size="lg" 
          :model-value="phoneForm.values.phone"
          @update:model-value="phoneForm.setFieldValue('phone', $event)" 
        />
      </UFormField>

      <UButton type="submit" block size="lg" :loading="phoneForm.isLoading.value" class="!py-3">
        Получить код
      </UButton>

      <div class="text-center text-sm text-gray-400">
        <button type="button" class="text-primary-500 hover:underline" @click="emit('switchToEmail')">
          Вернуться к входу по email
        </button>
      </div>
    </form>

    <form v-if="step === 'verification'" @submit="codeForm.handleSubmit" class="space-y-5">
      <UAlert v-if="codeForm.errors.value._general" color="error" variant="soft" :title="codeForm.errors.value._general" class="mb-4" />

      <div class="text-sm text-gray-400 mb-4">
        Код отправлен на номер <span class="font-medium text-white">{{ phoneForm.values.phone }}</span>
      </div>

      <UFormField label="Код подтверждения" :error="codeForm.errors.value.code">
        <UInput 
          placeholder="Введите код из SMS" 
          class="w-full" 
          size="lg" 
          maxlength="6"
          :model-value="codeForm.values.code"
          @update:model-value="codeForm.setFieldValue('code', $event)" 
        />
      </UFormField>

      <UButton type="submit" block size="lg" :loading="codeForm.isLoading.value" class="!py-3">
        Подтвердить
      </UButton>

      <div class="text-center text-sm text-gray-400">
        <button type="button" class="text-primary-500 hover:underline" @click="step = 'phone'; codeForm.reset()">
          Изменить номер
        </button>
      </div>
    </form>
  </div>
</template>
