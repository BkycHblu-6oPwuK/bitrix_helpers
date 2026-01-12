<script setup lang="ts">
import type { UserLoginEmailState } from '~/types/user';

const emit = defineEmits<{
  success: []
  switchToRegister: []
}>()

const userStore = useUserStore()

const form = useForm<UserLoginEmailState>({
  initialValues: {
    email: '',
    password: '',
  },
  validate: (values) => {
    const errors: any = {}
    if (!values.email) errors.email = 'Email обязателен'
    if (!values.password) errors.password = 'Пароль обязателен'
    return Object.keys(errors).length ? errors : null
  },
  onSubmit: async (values) => {
    await userStore.login('email', values)
    emit('success')
  }
})

defineExpose({ reset: form.reset, clearErrors: form.clearErrors })
</script>

<template>
  <form @submit="form.handleSubmit" class="space-y-5">
    <UAlert v-if="form.errors.value._general" color="error" variant="soft" :title="form.errors.value._general"
      class="mb-4" />

    <UFormField label="Email" :error="form.errors.value.email">
      <UInput placeholder="Введите email" class="w-full" size="lg" :model-value="form.values.email"
        @update:model-value="form.setFieldValue('email', $event)" />
    </UFormField>

    <UFormField label="Пароль" :error="form.errors.value.password">
      <UInput type="password" class="w-full" placeholder="Введите пароль" size="lg" :model-value="form.values.password"
        @update:model-value="form.setFieldValue('password', $event)" />
    </UFormField>

    <UButton type="submit" block size="lg" :loading="form.isLoading.value" class="!py-3 text-base">
      Войти
    </UButton>

    <div class="text-center text-sm text-gray-400">
      Нет аккаунта?
      <button type="button" class="text-primary-500 hover:underline ml-1" @click="emit('switchToRegister')">
        Зарегистрироваться
      </button>
    </div>
  </form>
</template>
