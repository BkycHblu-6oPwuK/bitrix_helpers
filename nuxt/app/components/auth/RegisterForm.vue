<script setup lang="ts">
import type { UserRegisterEmailState } from '~/types/user';

const emit = defineEmits<{
  success: []
  switchToLogin: []
}>()

const userStore = useUserStore()

const form = useForm<UserRegisterEmailState>({
  initialValues: {
    name: '',
    email: '',
    password: '',
  },
  validate: (values) => {
    const errors: any = {}
    if (!values.name) errors.name = 'Имя обязательно'
    if (!values.email) errors.email = 'Email обязателен'
    if (!values.password) errors.password = 'Пароль обязателен'
    if (values.password && values.password.length < 6) {
      errors.password = 'Минимум 6 символов'
    }
    return Object.keys(errors).length ? errors : null
  },
  onSubmit: async (values) => {
    await userStore.register('email', values)
    emit('success')
  }
})

defineExpose({ reset: form.reset, clearErrors: form.clearErrors })
</script>

<template>
  <form @submit="form.handleSubmit" class="space-y-5">
    <UAlert v-if="form.errors.value._general" color="error" variant="soft" :title="form.errors.value._general" class="mb-4" />

    <UFormField label="Имя" :error="form.errors.value.name">
      <UInput 
        placeholder="Введите имя" 
        class="w-full" 
        size="lg" 
        :model-value="form.values.name"
        @update:model-value="form.setFieldValue('name', $event)" 
      />
    </UFormField>

    <UFormField label="Email" :error="form.errors.value.email">
      <UInput 
        placeholder="Введите email" 
        class="w-full" 
        size="lg" 
        :model-value="form.values.email"
        @update:model-value="form.setFieldValue('email', $event)" 
      />
    </UFormField>

    <UFormField label="Пароль" :error="form.errors.value.password">
      <UInput 
        type="password" 
        class="w-full" 
        placeholder="Введите пароль" 
        size="lg"
        :model-value="form.values.password"
        @update:model-value="form.setFieldValue('password', $event)" 
      />
    </UFormField>

    <UButton type="submit" block size="lg" :loading="form.isLoading.value" class="!py-3 text-base">
      Зарегистрироваться
    </UButton>

    <div class="text-center text-sm text-gray-400">
      Уже есть аккаунт?
      <button type="button" class="text-primary-500 hover:underline ml-1" @click="emit('switchToLogin')">
        Войти
      </button>
    </div>
  </form>
</template>
