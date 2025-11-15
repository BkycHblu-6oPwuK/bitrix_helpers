<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const authType = ref<'login' | 'register'>('login')
const email = ref('')
const password = ref('')
const name = ref('')
const phone = ref('')
const loading = ref(false)

const switchToLogin = () => {
  authType.value = 'login'
}

const switchToRegister = () => {
  authType.value = 'register'
}

const onLogin = async () => {
  loading.value = true
  try {
    // TODO: Реализовать логику авторизации
    console.log('Login:', { email: email.value, password: password.value })
    await new Promise(resolve => setTimeout(resolve, 1000))
    // После успешной авторизации закрыть модальное окно
    isOpen.value = false
  } catch (error) {
    console.error('Login error:', error)
  } finally {
    loading.value = false
  }
}

const onRegister = async () => {
  loading.value = true
  try {
    // TODO: Реализовать логику регистрации
    console.log('Register:', { 
      name: name.value, 
      email: email.value, 
      phone: phone.value,
      password: password.value 
    })
    await new Promise(resolve => setTimeout(resolve, 1000))
    // После успешной регистрации закрыть модальное окно
    isOpen.value = false
  } catch (error) {
    console.error('Register error:', error)
  } finally {
    loading.value = false
  }
}

const onSocialAuth = (provider: string) => {
  console.log('Social auth:', provider)
  // TODO: Реализовать социальную авторизацию
}
</script>

<template>
  <UModal v-model="isOpen">
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold">
            {{ authType === 'login' ? 'Вход' : 'Регистрация' }}
          </h3>
          <UButton
            color="neutral"
            variant="ghost"
            icon="i-heroicons-x-mark"
            @click="isOpen = false"
          />
        </div>
      </template>

      <!-- Вход -->
      <div v-if="authType === 'login'" class="space-y-4">
        <UFormGroup label="Email" required>
          <UInput
            v-model="email"
            type="email"
            placeholder="your@email.com"
            icon="i-heroicons-envelope"
          />
        </UFormGroup>

        <UFormGroup label="Пароль" required>
          <UInput
            v-model="password"
            type="password"
            placeholder="••••••••"
            icon="i-heroicons-lock-closed"
          />
        </UFormGroup>

        <div class="flex justify-between items-center text-sm">
          <UCheckbox label="Запомнить меня" />
          <UButton variant="link" size="xs" :padded="false">
            Забыли пароль?
          </UButton>
        </div>

        <UButton
          block
          size="lg"
          :loading="loading"
          @click="onLogin"
        >
          Войти
        </UButton>

        <UDivider label="или" />

        <div class="space-y-2">
          <UButton
            block
            color="neutral"
            variant="outline"
            icon="i-simple-icons-google"
            @click="onSocialAuth('google')"
          >
            Войти через Google
          </UButton>
          <UButton
            block
            color="neutral"
            variant="outline"
            icon="i-simple-icons-vk"
            @click="onSocialAuth('vk')"
          >
            Войти через VK
          </UButton>
        </div>

        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
          Нет аккаунта?
          <UButton variant="link" size="xs" :padded="false" @click="switchToRegister">
            Зарегистрироваться
          </UButton>
        </div>
      </div>

      <!-- Регистрация -->
      <div v-else class="space-y-4">
        <UFormGroup label="Имя" required>
          <UInput
            v-model="name"
            placeholder="Иван Иванов"
            icon="i-heroicons-user"
          />
        </UFormGroup>

        <UFormGroup label="Email" required>
          <UInput
            v-model="email"
            type="email"
            placeholder="your@email.com"
            icon="i-heroicons-envelope"
          />
        </UFormGroup>

        <UFormGroup label="Телефон">
          <UInput
            v-model="phone"
            type="tel"
            placeholder="+7 (999) 999-99-99"
            icon="i-heroicons-phone"
          />
        </UFormGroup>

        <UFormGroup label="Пароль" required>
          <UInput
            v-model="password"
            type="password"
            placeholder="••••••••"
            icon="i-heroicons-lock-closed"
          />
        </UFormGroup>

        <UButton
          block
          size="lg"
          :loading="loading"
          @click="onRegister"
        >
          Зарегистрироваться
        </UButton>

        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
          Уже есть аккаунт?
          <UButton variant="link" size="xs" :padded="false" @click="switchToLogin">
            Войти
          </UButton>
        </div>
      </div>
    </UCard>
  </UModal>
</template>
