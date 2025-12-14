<script setup lang="ts">
import type { AuthMode } from '~/types/user'

const modelValue = defineModel<boolean>()
const emit = defineEmits<{ close: [] }>()

const userStore = useUserStore()

const mode = ref<AuthMode>('login')
const loginFormRef = ref()
const registerFormRef = ref()
const phoneFormRef = ref()

const handleSuccess = () => {
  modelValue.value = false
  emit('close')
}

const switchMode = (newMode: AuthMode) => {
  mode.value = newMode
  loginFormRef.value?.clearErrors()
  registerFormRef.value?.clearErrors()
  phoneFormRef.value?.clearErrors()
}

watch(modelValue, async (value) => {
  if (value) {
    await userStore.loadAuthMethods()
    mode.value = userStore.defaultAuthMode
  } else {
    mode.value = 'login'
    loginFormRef.value?.reset()
    registerFormRef.value?.reset()
    phoneFormRef.value?.reset()
  }
})
</script>

<template>
    <UModal v-model:open="modelValue">
        <template #header>
            <div class="w-full flex items-center justify-between mb-2">
                <h3 class="text-xl font-semibold">
                    {{ mode === 'register' ? 'Регистрация' : 'Вход' }}
                </h3>

                <UButton icon="i-heroicons-x-mark" color="info" variant="ghost" class="!p-2"
                    @click="modelValue = false; emit('close')" />
            </div>
        </template>
        <template #body>
            <div v-if="userStore.isLoadingMethods" class="flex justify-center items-center py-8">
                <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 animate-spin" />
            </div>

            <div v-else>
                <div v-if="mode !== 'register' && (userStore.hasEmailAuth || userStore.hasPhoneAuth)" 
                     class="grid gap-3 mb-6" 
                     :class="userStore.hasEmailAuth && userStore.hasPhoneAuth ? 'grid-cols-2' : 'grid-cols-1'">
                    <UButton 
                      v-if="userStore.hasEmailAuth"
                      :variant="mode === 'login' ? 'solid' : 'outline'" 
                      block 
                      size="lg" 
                      @click="switchMode('login')"
                    >
                        Email
                    </UButton>

                    <UButton 
                      v-if="userStore.hasPhoneAuth"
                      :variant="mode === 'phone' ? 'solid' : 'outline'" 
                      block 
                      size="lg" 
                      @click="switchMode('phone')"
                    >
                        Телефон
                    </UButton>
                </div>

                <AuthLoginForm
                  v-if="mode === 'login' && userStore.hasEmailAuth"
                  ref="loginFormRef"
                  @success="handleSuccess"
                  @switch-to-register="switchMode('register')"
                />

                <AuthRegisterForm
                  v-if="mode === 'register' && userStore.hasEmailAuth"
                  ref="registerFormRef"
                  @success="handleSuccess"
                  @switch-to-login="switchMode('login')"
                />

                <AuthPhoneLoginForm
                  v-if="mode === 'phone' && userStore.hasPhoneAuth"
                  ref="phoneFormRef"
                  @success="handleSuccess"
                  @switch-to-email="switchMode('login')"
                />

                <AuthSocialAuth
                  v-if="mode === 'login'"
                  :methods="userStore.socialAuthMethods"
                />

                <UAlert 
                  v-if="!userStore.hasAnyAuthMethod"
                  color="error"
                  variant="soft"
                  title="Методы авторизации не настроены"
                  class="mb-4"
                />
            </div>
        </template>
    </UModal>

</template>
